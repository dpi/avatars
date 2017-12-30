<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarKitEntityMap;
use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Entity\AvatarKitServiceInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\FieldConfigInterface;

/**
 * Drupal form alters.
 */
class AvatarKitFormAlter implements AvatarKitFormAlterInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   * @see \Drupal\avatars\AvatarKitFormAlter::entityFieldHandler
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function fieldConfigEditForm(array &$form, FormStateInterface $form_state): void {
    $field_config = $this->getFieldConfig($form_state);

    $avatar_field_types = ['file', 'image'];
    if (!in_array($field_config->getType(), $avatar_field_types)) {
      // Only add our form alters if this is a valid avatar field target.
      return;
    }

    $target_entity_type_id = $field_config->getTargetEntityTypeId();
    $targetBundle = $field_config->getTargetBundle();

    // Only show this form if this type is in the active map.
    $entityMap = AvatarKitEntityMap::load($target_entity_type_id . '.' . $targetBundle . '.' . 'default');
    $entityMapFieldName = $entityMap ? $entityMap->getFieldName() : NULL;
    if ($entityMapFieldName != $field_config->getName()) {
      return;
    }

    $form['avatars'] = [
      '#type' => 'details',
      '#title' => $this->t('Avatars'),
      '#tree' => TRUE,
      '#open' => TRUE,
      '#weight' => 50,
    ];

    $third_party = $field_config->getThirdPartySettings('avatars');
    $form['avatars']['hash'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier string'),
      '#description' => $this->t('Generate avatar hashes for @entity_type_plural based on this string.', [
        '@entity_type_plural' => $this->entityTypeManager()->getDefinition($target_entity_type_id)->getPluralLabel(),
      ]),
      '#default_value' => $third_party['hash']['contents'] ?? NULL,
    ];

    if ($this->moduleHandler()->moduleExists('token')) {
      // Add the token tree UI.
      $form['avatars']['token_tree'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => [$target_entity_type_id],
        '#show_nested' => FALSE,
        '#global_types' => FALSE,
      ];
    }

    $form['#attached']['library'][] = 'avatars/avatars.admin';
    $form['avatars']['services'] = $this->buildServiceTable($field_config);

    // Our submission function needs to be before
    // \Drupal\field_ui\Form\FieldConfigEditForm::save.
    array_unshift(
      $form['actions']['submit']['#submit'],
      [$this, 'fieldConfigEditFormSubmit']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fieldConfigEditFormSubmit(array $form, FormStateInterface $form_state): void {
    $field_config = $this->getFieldConfig($form_state);
    $hash['contents'] = $form_state->getValue(['avatars', 'hash']);
    $field_config->setThirdPartySetting('avatars', 'hash', $hash);

    $enabledServices = [];
    $service_info = $form_state->getValue(['avatars', 'services']);
    foreach ($service_info as $service_id => $info) {
      ['status' => $status, 'weight' => $weight] = $info;
      // Ignore disabled services.
      if ($status == 'enabled') {
        $enabledServices[$weight] = $service_id;
      }
    }

    ksort($enabledServices, SORT_NUMERIC);

    $field_config->setThirdPartySetting('avatars', 'services', $enabledServices);

    // Reset avatar preferences.
    $entityTypeId = $field_config->getTargetEntityTypeId();
    $bundle = $field_config->getTargetBundle();
    $this->entityPreferenceManager()->invalidatePreferences(
      $field_config->getTargetEntityTypeId(),
      $bundle
    );

    // Reset entity render caches.
    $viewTags = [$entityTypeId . '_view'];
    $listTags = $this->entityTypeManager()->getDefinition($entityTypeId)->getListCacheTags();
    $tags = Cache::mergeTags($viewTags, $listTags);
    Cache::invalidateTags($tags);
  }

  /**
   * Get the field config entity.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The field config entity.
   */
  public function getFieldConfig(FormStateInterface $form_state): FieldConfigInterface {
    /** @var \Drupal\field_ui\Form\FieldConfigEditForm $form_object */
    $form_object = $form_state->getFormObject();
    return $form_object->getEntity();
  }

  /**
   * Get the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager(): EntityTypeManagerInterface {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }
    return $this->entityTypeManager;
  }

  /**
   * Get the entity preference manager.
   *
   * @return \Drupal\avatars\AvatarKitEntityPreferenceManagerInterface
   *   The entity preference manager.
   */
  protected function entityPreferenceManager(): AvatarKitEntityPreferenceManagerInterface {
    return \Drupal::service('avatars.entity_preference');
  }

  /**
   * Get the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler(): ModuleHandlerInterface {
    return \Drupal::moduleHandler();
  }

  /**
   * Build a weightable + grouped avatar services table.
   *
   * @param \Drupal\field\FieldConfigInterface $field_config
   *   The field configuration.
   *
   * @return array
   *   A render array.
   */
  protected function buildServiceTable(FieldConfigInterface $field_config): array {
    $headers = [
      'label' => $this->t('Service'),
      'weight' => $this->t('Weight'),
      'status' => $this->t('Status'),
    ];

    $table_drag_group = 'avatar-service-weight';

    $table = [
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
      '#attributes' => [
        'id' => 'xyz',
      ],
    ];

    $existingServices = $field_config->getThirdPartySetting('avatars', 'services', []);

    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface[] $instances */
    $instances = AvatarKitService::loadMultiple();

    // Group services according to whether they appear in this field config.
    $groupedServices = [];

    // Create an empty 'enabled' array so it appears at the top.
    $groupedServices['enabled'] = [];

    foreach ($instances as $instance) {
      $group = in_array($instance->id(), $existingServices) ? 'enabled' : 'disabled';
      $groupedServices[$group][] = $instance;
    }

    // Sort services according to their order in field config (for enabled).
    // This does nothing for disabled services.
    foreach ($groupedServices as $group => &$values) {
      usort($values, function (AvatarKitServiceInterface $a, AvatarKitServiceInterface $b) use ($existingServices) {
        $a_key = array_search($a->id(), $existingServices);
        $b_key = array_search($b->id(), $existingServices);
        return $a_key < $b_key ? -1 : 1;
      });
    }

    $regionsTranslated = [
      'disabled' => $this->t('Disabled'),
      'enabled' => $this->t('Enabled'),
    ];

    foreach ($groupedServices as $region => $services) {
      $table[$region]['#attributes']['data-tabledrag-region'] = $region;
      $table[$region][] = [
        '#type' => 'inline_template',
        '#template' => '<strong>{{ region }}</strong>',
        '#context' => [
          'region' => $regionsTranslated[$region],
        ],
        '#wrapper_attributes' => [
          'colspan' => count($headers),
        ],
      ];

      /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface $service */
      foreach ($services as $weight => $service) {
        $row = [];

        $row['#attributes']['class'][] = 'draggable';
        $row['label']['#plain_text'] = $service->label();

        $row['weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight'),
          '#title_display' => 'invisible',
          '#default_value' => $weight,
          '#attributes' => [
            'class' => [$table_drag_group, 'tabledrag-region-weight'],
          ],
        ];

        $row['status'] = [
          '#type' => 'select',
          '#title' => $this->t('Status'),
          '#title_display' => 'invisible',
          '#default_value' => $region,
          '#options' => $regionsTranslated,
          '#attributes' => [
            'class' => [
              'tabledrag-region-value',
            ],
          ],
        ];

        $id = $service->id();
        $table[$id] = $row;
      }
    }

    return $table;
  }

}
