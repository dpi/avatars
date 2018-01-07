<?php

namespace Drupal\avatars\Form;

use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Entity\AvatarKitServiceInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Avatar Kit services.
 */
class AvatarKitServicesForm extends ConfigFormBase {

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
      'plugin' => $this->t('Plugin'),
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
      '#default_value' => [],
    ];

    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface[] $instances */
    $instances = AvatarKitService::loadMultiple();
    // Sort alphabetically by label.
    uasort($instances, function (AvatarKitServiceInterface $a, AvatarKitServiceInterface $b) {
      return strnatcasecmp($a->label(), $b->label());
    });
    foreach ($instances as $instance) {
      $row = [];

      $row['label']['#plain_text'] = $instance->label();

      $definition = $instance->getPlugin()->getPluginDefinition();
      $row['plugin']['#plain_text'] = $definition['label'] ?? '';

      $row['operations'] = [
        '#type' => 'operations',
        '#links' => $this->getOperations($instance),
      ];

      $id = $instance->id();
      $form['services'][$id] = $row;
    }

    $form = parent::buildForm($form, $form_state);
    unset($form['actions']);
    return $form;
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
