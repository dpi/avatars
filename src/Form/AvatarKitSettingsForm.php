<?php

namespace Drupal\avatars\Form;

use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Entity\AvatarKitServiceInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Avatar Kit settings.
 */
class AvatarKitSettingsForm extends ConfigFormBase {

  /**
   * The avatar service preference cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $preferenceCacheBackend;

  /**
   * Construct a new AvatarKitServicesForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Cache\CacheBackendInterface $preferenceCacheBackend
   *   The avatar service preference cache backend.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $preferenceCacheBackend) {
    parent::__construct($config_factory);
    $this->preferenceCacheBackend = $preferenceCacheBackend;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cache.avatars.entity_preference')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() : array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return 'avatars_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) : array {
    $headers = [
      'label' => $this->t('Service'),
      'weight' => $this->t('Weight'),
    ];

    $table_drag_group = 'avatar-service-weight';
    $form['service_weights'] = [
      '#type' => 'details',
      '#title' => $this->t('Service weights'),
      '#description' => $this->t('These weights apply to non-entities. Where services closest to the top are executed first. For entities, edit weights on field settings page. A list of field settings is available in <a href=":entity-mapping">Entity mapping</a>.', [
        ':entity-mapping' => Url::fromRoute('avatars.config.entity_map')->toString(),
      ]),
      '#open' => TRUE,
    ];
    $form['service_weights']['services'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#empty' => $this->t('No avatar service instances found.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $table_drag_group,
        ],
      ],
      '#default_value' => [],
    ];

    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface[] $instances */
    $instances = AvatarKitService::loadMultiple();
    uasort($instances, [AvatarKitService::class, 'sort']);
    foreach ($instances as $instance) {
      $row = [];

      $row['#attributes']['class'][] = 'draggable';
      $row['label']['#plain_text'] = $instance->label();
      $row['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $instance->getWeight(),
        '#attributes' => [
          'class' => [$table_drag_group],
        ],
      ];

      $id = $instance->id();
      $form['service_weights']['services'][$id] = $row;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    parent::submitForm($form, $form_state);

    // Invalidate cached preferences for entities.
    $this->preferenceCacheBackend->invalidateAll();

    // Generators are already sorted correctly.
    foreach ($form_state->getValue('services') as $id => $row) {
      /** @var \Drupal\avatars\Entity\AvatarKitService $instance */
      $instance = AvatarKitService::load($id);
      $instance
        ->setWeight($row['weight'])
        ->save();
    }
  }

}
