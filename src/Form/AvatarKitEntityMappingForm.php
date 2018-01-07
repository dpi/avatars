<?php

namespace Drupal\avatars\Form;

use Drupal\avatars\Entity\AvatarKitEntityMap;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Configure Avatar Kit entity maps.
 */
class AvatarKitEntityMappingForm extends ConfigFormBase {

  /**
   * Storage for entity mapping configuration entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityMappingStorage;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * AvatarKitEntityMappingForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($config_factory);
    $this->entityMappingStorage = $entityTypeManager->getStorage('avatars_entity_mapping');
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
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
    return 'avatars_entity_mapping_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) : array {
    $form = parent::buildForm($form, $form_state);

    $avatar_field_types = ['file', 'image'];

    $headers = [
      'label' => $this->t('Entity type'),
      'weight' => $this->t('Bundle'),
      'field' => $this->t('Field'),
      'field_settings' => $this->t('Field settings'),
      'debug' => $this->t('Debug'),
    ];

    $form['map'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#empty' => $this->t('No entity types with suitable avatar field targets found.'),
      '#default_value' => [],
    ];

    $fieldMap = $this->entityFieldManager->getFieldMap();
    unset($fieldMap['avatars_avatar_cache']);

    $fieldsOptions = [];
    foreach ($fieldMap as $entityType => $fields) {
      foreach ($fields as $fieldName => $fieldInfo) {
        ['type' => $type, 'bundles' => $bundles] = $fieldInfo;

        if (!in_array($type, $avatar_field_types)) {
          continue;
        }

        foreach ($bundles as $bundle) {
          $key = $entityType . ':' . $bundle;
          $fieldsOptions[$key][$fieldName] = $this->t('@field_name (@field_type)', [
            '@field_name' => $fieldName,
            '@field_type' => $type,
          ]);
        }
      }
    }

    foreach ($fieldsOptions as $key => $options) {
      [$entityType, $bundle] = explode(':', $key);

      $id = $entityType . '.' . $bundle . '.default';
      $entityMap = $this->entityMappingStorage->load($id);
      $entityMapFieldName = $entityMap ? $entityMap->getFieldName() : NULL;

      $row = [];
      $row['entity_type']['#plain_text'] = $entityType;
      $row['bundle']['#plain_text'] = $bundle;
      $row['field'] = [
        '#type' => 'select',
        '#title' => $this->t('Field'),
        '#title_display' => 'invisible',
        '#default_value' => $entityMapFieldName,
        '#options' => $options,
        '#empty_option' => $this->t('- None -'),
      ];

      $row['field_settings'] = [];
      if ($entityMapFieldName) {
        $fieldConfigId = $entityType . '.' . $bundle . '.' . $entityMapFieldName;
        try {
          $url = Url::fromRoute('entity.field_config.' . $entityType . '_field_edit_form')
            ->setRouteParameter('field_config', $fieldConfigId);
          $row['field_settings']['link'] = [
            '#type' => 'link',
            '#title' => $this->t('Settings'),
            '#url' => $url,
          ];
        }
        catch (RouteNotFoundException $exception) {
          // When field_ui is not enabled.
        }
      }

      $url = Url::fromRoute('avatars.config.entity_map.entity_query')
        ->setRouteParameter('entity_type_id', $entityType);
      $row['debug']['link'] = [
        '#type' => 'link',
        '#title' => $this->t('Entity query'),
        '#url' => $url,
      ];

      $form['map'][$key] = $row;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\avatars\Entity\AvatarKitEntityMapInterface[] $entityMaps */
    $entityMaps = $this->entityMappingStorage->loadMultiple();

    $map = $form_state->getValue('map');
    foreach ($map as $key => $values) {
      [$entityType, $bundle] = explode(':', $key);
      $mapKey = $entityType . '.' . $bundle . '.default';

      // Check if a map exists already.
      $entityMap = $entityMaps[$mapKey] ?? NULL;

      $fieldName = $values['field'];
      if (!$fieldName) {
        continue;
      }

      unset($entityMaps[$mapKey]);

      if (!$entityMap) {
        // Otherwise create one.
        $entityMap = $this->entityMappingStorage->create([
          'entity_type' => $entityType,
          'bundle' => $bundle,
        ]);
      }

      $entityMap->set('field_name', $fieldName);
      $entityMap->save();
    }

    // Delete any left over mappings.
    foreach ($entityMaps as $entityMap) {
      $entityMap->delete();
    }
  }

}
