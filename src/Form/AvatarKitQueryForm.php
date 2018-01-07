<?php

namespace Drupal\avatars\Form;

use Drupal\avatars\AvatarKitEntityHandlerInterface;
use Drupal\avatars\AvatarKitEntityPreferenceManagerInterface;
use Drupal\avatars\AvatarKitLocalCacheInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Avatar Kit entity query.
 */
class AvatarKitQueryForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The avatar entity handler.
   *
   * @var \Drupal\avatars\AvatarKitEntityHandlerInterface
   */
  protected $entityHandler;

  /**
   * Avatar Kit local cache.
   *
   * @var \Drupal\avatars\AvatarKitLocalCacheInterface
   */
  protected $entityLocalCache;

  /**
   * Avatar Kit preference manager.
   *
   * @var \Drupal\avatars\AvatarKitEntityPreferenceManagerInterface
   */
  protected $preferenceManager;

  /**
   * Constructs a new AvatarKitEntityFieldHandler instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\avatars\AvatarKitEntityHandlerInterface $entityHandler
   *   The avatar entity handler.
   * @param \Drupal\avatars\AvatarKitLocalCacheInterface $entityLocalCache
   *   Avatar Kit local cache.
   * @param \Drupal\avatars\AvatarKitEntityPreferenceManagerInterface $preferenceManager
   *   Avatar Kit preference manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AvatarKitEntityHandlerInterface $entityHandler, AvatarKitLocalCacheInterface $entityLocalCache, AvatarKitEntityPreferenceManagerInterface $preferenceManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityHandler = $entityHandler;
    $this->entityLocalCache = $entityLocalCache;
    $this->preferenceManager = $preferenceManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('avatars.entity'),
      $container->get('avatars.local_cache'),
      $container->get('avatars.entity_preference')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'avatars_entity_query';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $entity_type_id = NULL): array {
    if (!$entity_type_id) {
      throw new \Exception('Entity type ID must be defined.');
    }
    try {
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
    }
    catch (\Exception $exception) {
      // Entity type does not exist.
      throw new NotFoundHttpException();
    }

    $form['entity'] = [
      '#tree' => TRUE,
      '#type' => 'item',
      '#field_prefix' => '<div class="container-inline">',
      '#field_suffix' => '</div>',
    ];
    $form['entity']['entity_id'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Label'),
      '#target_type' => $entity_type_id,
    ];
    $form['entity']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    $entity_id = $form_state->getValue(['entity', 'entity_id']);
    if ($entity_id) {
      $entity = $storage->load($entity_id);

      $form['debug']['preferences'] = $this->preferenceTable($entity);

      $form['debug'][] = [
        '#type' => 'inline_template',
        '#template' => '<h2>{{ heading2 }}</h2>',
        '#context' => ['heading2' => $this->t('Ordered cache items')],
      ];

      $headers = [
        $this->t('#'),
        $this->t('Cache ID'),
        $this->t('Service'),
        $this->t('File'),
        $this->t('Due for update'),
        $this->t('Status'),
      ];
      $form['debug']['cache_items'] = [
        '#type' => 'table',
        '#header' => $headers,
        '#empty' => $this->t('No cache items found.'),
        '#default_value' => [],
      ];

      $this->entityHandler->setReadOnly(TRUE);
      $cacheItems = $this->entityHandler->findAll($entity);

      $i = 0;
      $selected = FALSE;
      foreach ($cacheItems as $cacheItem) {
        $i++;
        $row = [];
        $row['row_number']['#plain_text'] = $i;
        $row['id']['#plain_text'] = $cacheItem->id();
        $row['service']['#plain_text'] = $cacheItem->getAvatarServiceId();

        $avatar = $cacheItem->getAvatar();
        if ($avatar) {
          $row[]['#plain_text'] = $this->t('File @id', ['@id' => $avatar->id()]);
        }
        else {
          $row[]['#plain_text'] = $this->t('None');
        }

        $row[]['#plain_text'] = $this->entityLocalCache->cacheNeedsUpdate($cacheItem) ? $this->t('Yes') : $this->t('No');

        if ($avatar && !$selected) {
          $selected = TRUE;
          $row[]['#plain_text'] = $this->t('Selected');
        }
        else {
          $row[]['#plain_text'] = $this->t('Ignored');
        }

        $form['debug']['cache_items'][] = $row;
      }

      // Read only must be reset *after* iterating cache items, since generators
      // run on demand.
      $this->entityHandler->setReadOnly(FALSE);
    }

    $form['help1']['#prefix'] = '<p>';
    $form['help1']['#plain_text'] = $this->t("If you do not see any cache items, then avatars for entity may not have been fetched.");
    $form['help1']['#suffix'] = '</p>';
    $form['help2']['#prefix'] = '<p>';
    $form['help2']['#plain_text'] = $this->t("If you are seeing a different avatar than the file in the 'Selected' row, then you may need to wait for the render cache to reset for that entity. Or you can clear caches manually.");
    $form['help2']['#suffix'] = '</p>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    $form_state->setRebuild();
  }

  /**
   * Generate the service preference table for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   *
   * @return array
   *   A render array.
   */
  protected function preferenceTable(EntityInterface $entity): array {
    $headers = [
      $this->t('#'),
      $this->t('Service'),
    ];

    $table = [
      '#type' => 'table',
      '#header' => $headers,
      '#empty' => $this->t('No preferences found.'),
    ];

    $preferences = $this->preferenceManager->getPreferences($entity);
    $i = 0;
    foreach ($preferences as $service_id) {
      $i++;
      $table[$i][]['#plain_text'] = $i;
      $table[$i][]['#plain_text'] = $service_id;
    }

    return [
      [
        '#type' => 'inline_template',
        '#template' => '<h2>{{ heading2 }}</h2>',
        '#context' => ['heading2' => $this->t('Service order')],
      ],
      $table
    ];
  }

}
