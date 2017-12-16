<?php

namespace Drupal\avatars\Form\Entity;

use Drupal\avatars\AvatarKitServicePluginManagerInterface;
use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Entity\AvatarKitServiceInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add and edit form for Avatar Kit service instances.
 */
class AvatarKitServiceForm extends EntityForm {

  /**
   * Avatar Kit service plugin manager.
   *
   * @var \Drupal\avatars\AvatarKitServicePluginManagerInterface
   */
  protected $avatarPluginManager;

  /**
   * Create a new AvatarKitServiceForm.
   *
   * @param \Drupal\avatars\AvatarKitServicePluginManagerInterface $avatarPluginManager
   *   Avatar Kit service plugin manager.
   */
  public function __construct(AvatarKitServicePluginManagerInterface $avatarPluginManager) {
    $this->avatarPluginManager = $avatarPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.avatars_services')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) : array {
    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface $instance */
    $instance = $this->getEntity();
    $is_new = $instance->isNew();

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $instance->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $instance->id(),
      '#machine_name' => [
        'source' => ['label'],
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores.',
      ],
      '#disabled' => !$is_new,
    ];

    $definitions = $this->avatarPluginManager->getDefinitions();
    $plugins = array_map(
      function (array $definition) : string {
        return $definition['label'];
      },
      $definitions
    );

    if ($is_new) {
      $form['plugin'] = [
        '#type' => 'radios',
        '#title' => $this->t('Service'),
        '#options' => $plugins,
        '#required' => TRUE,
      ];
    }

    $plugin = $instance->getPlugin();
    if ($plugin) {
      $form['plugin_configuration'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Settings'),
        '#tree' => TRUE,
      ];

      $subform_state = SubformState::createForSubform($form['plugin_configuration'], $form, $form_state);
      $form['plugin_configuration'] = $plugin->buildConfigurationForm($form['plugin_configuration'], $subform_state);
    }

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface $instance */
    $instance = $this->getEntity();
    $plugin = $instance->getPlugin();
    if ($plugin) {
      $subform_state = SubformState::createForSubform($form['plugin_configuration'], $form, $form_state);
      $plugin->validateConfigurationForm($form['plugin_configuration'], $subform_state);
    }
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) : int {
    /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface $instance */
    $instance = $this->getEntity();
    $plugin = $instance->getPlugin();
    if ($plugin) {
      $subform_state = SubformState::createForSubform($form['plugin_configuration'], $form, $form_state);
      $plugin->submitConfigurationForm($form['plugin_configuration'], $subform_state);
    }

    $status = parent::save($form, $form_state);

    if ($status === SAVED_NEW) {
      $form_state->setRedirectUrl($this->getEntity()->toUrl('edit-form'));
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($form_value, array $element, FormStateInterface $form_state) : bool {
    return AvatarKitService::load($form_value) instanceof AvatarKitServiceInterface;
  }

}
