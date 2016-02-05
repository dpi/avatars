<?php

/**
 * @file
 * Contains \Drupal\avatars\Form\Settings.
 */

namespace Drupal\avatars\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\avatars\AvatarGeneratorPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\unlimited_number\Element\UnlimitedNumber;
use Drupal\Core\Cache\Cache;
use Drupal\avatars\Entity\AvatarGenerator;

/**
 * Configure avatar kit settings.
 */
class Settings extends ConfigFormBase {

  /**
   * The avatar generator plugin manager.
   *
   * @var \Drupal\avatars\AvatarGeneratorPluginManagerInterface
   */
  protected $avatarGenerator;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\avatars\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AvatarGeneratorPluginManagerInterface $avatar_generator) {
    parent::__construct($config_factory);
    $this->avatarGenerator = $avatar_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.avatar_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'avatars_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'avatars.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('avatars.settings');

    // Define table.
    $headers = [
      'label' => $this->t('Avatar Generator'),
      'type' => $this->t('Type'),
      'plugin' => $this->t('Plugin'),
      'settings' => $this->t('Settings'),
      'enabled' => [
        'data' => $this->t('Enabled'),
        'class' => ['checkbox'],
      ],
      'weight' => $this->t('Weight'),
      'operations' => $this->t('Operations'),
    ];

    $form['avatar_generators_help'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('A list of avatar generators to try for each user in order of preference.'),
      '#suffix' => '</p>',
    ];
    $form['avatar_generators'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#empty' => $this->t('No avatar generators found.'),
      '#attributes' => [
        'id' => 'avatar-generators',
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'generator-weight',
        ],
      ],
    ];

    /** @var \Drupal\avatars\AvatarGeneratorInterface[] $instances */
    $instances = AvatarGenerator::loadMultiple();
    uasort($instances, '\Drupal\avatars\Entity\AvatarGenerator::sort');

    foreach ($instances as $instance) {
      $form['avatar_generators'][$instance->id()] = [];
      $row = &$form['avatar_generators'][$instance->id()];
      $row['#attributes']['class'][] = 'draggable';
      $definition = $instance->getPlugin()->getPluginDefinition();

      $row['label']['#markup'] = $instance->label();
      $row['type']['#markup'] = $definition['dynamic'] ? $this->t('Dynamic') : $this->t('Static');
      $row['plugin']['#markup'] = $definition['label'];
      $row['settings'] = $instance->getPlugin()->settingsSummary();

      $row['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enabled'),
        '#title_display' => 'invisible',
        '#default_value' => $instance->status(),
        '#wrapper_attributes' => [
          'class' => [
            'checkbox',
          ],
        ],
      ];
      $row['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $instance->getWeight(),
        '#attributes' => [
          'class' => ['generator-weight'],
        ],
      ];

      $operations = [];
      if ($instance->access('update')) {
        $operations['edit'] = array(
          'title' => $this->t('Edit'),
          'weight' => 10,
          'url' => $instance->toUrl('edit-form'),
        );
      }
      if ($instance->access('delete')) {
        $operations['delete'] = array(
          'title' => $this->t('Delete'),
          'weight' => 100,
          'url' => $instance->toUrl('delete-form'),
        );
      }
      $row['operations'] = [
        '#type' => 'operations',
        '#links' => $operations,
      ];
    }

    $form['refresh_interval']['#tree'] = TRUE;
    $intervals = $config->get('refresh_interval');
    $form['refresh_interval']['dynamic'] = [
      '#type' => 'number',
      '#title' => $this->t('Dynamic lifetime'),
      '#description' => $this->t('How long dynamic avatars are cached before allowing refresh.'),
      '#default_value' => $intervals['dynamic'],
      '#step' => 60,
      '#min' => 0,
      '#field_suffix' => $this->t('seconds'),
    ];

    /*
     * Keep unused avatars on file system (will use up more disk space)
     * Avatars must be purged manually if you change this settings.
     * Expire unused static avatars (will use up more network bandwidth)
     * */
    $form['refresh_interval']['static'] = [
      '#type' => 'unlimited_number',
      '#title' => $this->t('Static lifetime'),
      '#description' => $this->t('How long static avatars are cached. Only applies to avatars which are not the users preference.'),
      '#default_value' => $intervals['static'] < 1 ? UnlimitedNumber::UNLIMITED : $intervals['static'],
      '#step' => 60,
      '#min' => 60,
      '#field_suffix' => $this->t('seconds'),
      '#options' => [
        'unlimited' => $this->t('Never delete'),
        'limited' => $this->t('Delete after'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('avatars.settings');

    // Generators are already sorted correctly.
    foreach ($form_state->getValue('avatar_generators') as $id => $row) {
      /** @var \Drupal\avatars\AvatarGeneratorInterface $avatar_generator */
      $avatar_generator = AvatarGenerator::load($id);
      $avatar_generator
        ->setStatus($row['enabled'])
        ->setWeight($row['weight'])
        ->save();
    }

    Cache::invalidateTags(['avatar_preview']);

    $intervals = $form_state->getValue('refresh_interval');
    if ($intervals['static'] == UnlimitedNumber::UNLIMITED) {
      $intervals['static'] = 0;
    }

    $config->set('refresh_interval', [
      'dynamic' => $intervals['dynamic'],
      'static' => $intervals['static'],
    ]);
    $config->save();

    drupal_set_message(t('Settings saved.'));
  }

}
