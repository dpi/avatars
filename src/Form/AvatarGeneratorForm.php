<?php

/**
 * @file
 * Contains \Drupal\avatars\Form\AvatarGeneratorForm.
 */

namespace Drupal\avatars\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\avatars\AvatarGeneratorInterface;
use Drupal\avatars\AvatarGeneratorPluginManagerInterface;
use Drupal\avatars\Entity\AvatarGenerator;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\FormState;

/**
 * Form controller for avatar generator plugin instances.
 */
class AvatarGeneratorForm extends EntityForm {

  /**
   * The avatar generator plugin manager.
   *
   * @var \Drupal\avatars\AvatarGeneratorPluginManagerInterface
   */
  protected $avatarGenerator;

  /**
   * Constructs a \Drupal\avatars\Form\AvatarGeneratorForm object.
   *
   * @param \Drupal\avatars\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   */
  public function __construct(AvatarGeneratorPluginManagerInterface $avatar_generator) {
    $this->avatarGenerator = $avatar_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.avatar_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\avatars\AvatarGeneratorInterface $avatar_generator */
    $avatar_generator = $this->getEntity();

    if (!$avatar_generator->isNew()) {
      $form['#title'] = $this->t('Edit avatar generator %label', [
        '%label' => $avatar_generator->label(),
      ]);
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $avatar_generator->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $avatar_generator->id(),
      '#machine_name' => [
        'source' => ['label'],
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores.',
      ],
      '#disabled' => !$avatar_generator->isNew(),
    ];

    $plugins = [];
    foreach ($this->avatarGenerator->getDefinitions() as $plugin_id => $definition) {
      if ($plugin_id == 'broken') {
        continue;
      }
      $plugins[$plugin_id] = $this->t('<strong>@label</strong><br />@description', [
        '@label' => (string) $definition['label'],
        '@description' => (string) $definition['description'],
      ]);
    }
    unset($plugins['broken']);
    asort($plugins);

    if ($avatar_generator->isNew()) {
      $form['plugin'] = [
        '#type' => 'radios',
        '#title' => $this->t('Avatar generator'),
        '#options' => $plugins,
        '#required' => TRUE,
        '#disabled' => !$avatar_generator->isNew(),
      ];
    }
    else {
      $form['settings'] = $avatar_generator->getPlugin()
        ->buildConfigurationForm([], $form_state);
      $form['settings']['#tree'] = TRUE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\avatars\AvatarGeneratorInterface $avatar_generator */
    $avatar_generator = $this->getEntity();

    if ($avatar_generator->isNew()) {
      // Add a plugin ID so default plugin config will be added when saved.
      $avatar_generator = AvatarGenerator::create([
        'id' => $form_state->getValue('id'),
        'label' => $form_state->getValue('label'),
        'plugin' => $form_state->getValue('plugin'),
      ]);
      $this->setEntity($avatar_generator);
    }
    else {
      $settings = (new FormState())->setValues($form_state->getValue('settings', []));
      $avatar_generator->getPlugin()
        ->validateConfigurationForm($form, $settings);
      $form_state->setValue('settings', $settings->getValues());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\avatars\AvatarGeneratorInterface $avatar_generator */
    $avatar_generator = $this->getEntity();

    if (!$avatar_generator->isNew()) {
      $settings = (new FormState())->setValues($form_state->getValue('settings', []));
      $avatar_generator->getPlugin()
        ->submitConfigurationForm($form, $settings);
      $form_state->setValue('settings', $settings->getValues());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\avatars\AvatarGeneratorInterface $avatar_generator */
    $avatar_generator = $this->getEntity();
    $saved = $avatar_generator->save();

    $t_args['%label'] = $avatar_generator->label();
    $message = ($saved == SAVED_NEW) ? t('Created avatar generator %label', $t_args) : t('Updated avatar generator %label', $t_args);
    drupal_set_message($message);

    if ($saved == SAVED_NEW) {
      // Redirect to edit form.
      $form_state->setRedirectUrl(Url::fromRoute('entity.avatar_generator.edit_form', [
        'avatar_generator' => $avatar_generator->id(),
      ]));
    }
    else {
      $form_state->setRedirectUrl(Url::fromRoute('avatars.config'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function exists($form_value, array $element, FormStateInterface $form_state) {
    return AvatarGenerator::load($form_value) instanceof AvatarGeneratorInterface;
  }

}
