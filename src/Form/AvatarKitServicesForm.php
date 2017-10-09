<?php

namespace Drupal\avatars\Form;

use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Entity\AvatarKitServiceInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Avatar Kit services.
 */
class AvatarKitServicesForm extends ConfigFormBase {

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
      'plugin' => $this->t('Plugin'),
      'weight' => $this->t('Weight'),
      'operations' => $this->t('Operations'),
    ];

    $table_drag_group = 'avatar-service-weight';
    $form['services'] = [
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
    ];

    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface[] $instances */
    $instances = AvatarKitService::loadMultiple();
    uasort($instances, [AvatarKitService::class, 'sort']);
    foreach ($instances as $instance) {
      $row = [];

      $row['#attributes']['class'][] = 'draggable';
      $row['label']['#plain_text'] = $instance->label();

      $definition = $instance->getPlugin()->getPluginDefinition();
      $row['plugin']['#plain_text'] = $definition['label'] ?? '';

      $row['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $instance->getWeight(),
        '#attributes' => [
          'class' => [$table_drag_group],
        ],
      ];

      $row['operations'] = [
        '#type' => 'operations',
        '#links' => $this->getOperations($instance),
      ];

      $id = $instance->id();
      $form['services'][$id] = $row;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    parent::submitForm($form, $form_state);

    // Generators are already sorted correctly.
    foreach ($form_state->getValue('services') as $id => $row) {
      /** @var \Drupal\avatars\Entity\AvatarKitService $instance */
      $instance = AvatarKitService::load($id);
      $instance
        ->setWeight($row['weight'])
        ->save();
    }
  }

  /**
   * Get operations for an avatar service suitable for a operations element.
   *
   * @param \Drupal\avatars\Entity\AvatarKitServiceInterface $instance
   *   An avatar kit service instance.
   *
   * @return array
   *   Get operations for an avatar service instance suitable
   */
  protected function getOperations(AvatarKitServiceInterface $instance) : array {
    $operations = [];
    if ($instance->access('update')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $instance->toUrl('edit-form'),
      ];
    }
    if ($instance->access('delete')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => $instance->toUrl('delete-form'),
      ];
    }
    return $operations;
  }

}
