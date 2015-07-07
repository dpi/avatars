<?php

/**
 * @file
 * Contains \Drupal\ak\Form\Settings.
 */

namespace Drupal\ak\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ak\AvatarGeneratorPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\ak\AvatarPreviewInterface;

/**
 * Configure avatar kit settings.
 */

class Settings extends ConfigFormBase {

  /**
   * The avatar generator plugin manager.
   *
   * @var \Drupal\ak\AvatarGeneratorPluginManagerInterface
   */
  protected $avatarGenerator;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\ak\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   *
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
    return 'ak_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ak.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('ak.settings');

    $avatar_generators_fallback = [
      '' => $this->t('<em>Use uploaded image</em>'),
    ];
    $avatar_generators = [
      '' => $this->t('<em>Use fallback</em>'),
    ];
    foreach ($this->avatarGenerator->getDefinitions() as $plugin_id => $definition) {
      if ($plugin_id != 'broken') {
        $label = $this->t('@label (%dynamic)', [
          '@label' => $definition['label'],
          '%dynamic' => $definition['dynamic'] ? $this->t('Dynamic') : $this->t('Static'),
        ]);

        $avatar_generators[$plugin_id] = $label;
        if ($definition['fallback']) {
          $avatar_generators_fallback[$plugin_id] = $label;
        }
      }
    }

    $form['avatar_generator']['#tree'] = TRUE;
    $generators = $config->get('avatar_generator');
    $form['avatar_generator']['default'] = [
      '#type' => 'radios',
      '#options' => $avatar_generators,
      '#title' => $this->t('Default avatar generator'),
      '#description' => $this->t('Default avatar generator for users with no preference set.'),
      '#default_value' => $generators['default'],
    ];

    $form['avatar_generator']['fallback'] = [
      '#type' => 'radios',
      '#options' => $avatar_generators_fallback,
      '#title' => $this->t('Fallback avatar generator'),
      '#description' => $this->t("Avatar generator to use when the site default or users' preference does not produce an avatar."),
      // link to none: admin/config/people/accounts/fields/user.user.user_picture#edit-settings-default-image
      '#default_value' => $generators['fallback'],
    ];

    $image_styles = [];
    $form['image_style'] = [
      '#type' => 'select',
      '#options' => $image_styles,
      '#title' => $this->t('Preference form image style'),
      '#description' => $this->t('Select image style to use in the users avatar generator preference form.'),
    ];

    $form['refresh_interval']['#tree'] = TRUE;
    $intervals = $config->get('refresh_interval');
    $form['refresh_interval']['dynamic'] = [
      '#type' => 'number',
      '#title' => $this->t('Dynamic lifetime'),
      '#description' => $this->t('How long dynamic avatars are cached before allowing refresh.'),
      '#default_value' => $intervals['dynamic'],
      '#step' => 60,
      '#min' => 0,
    ];

    $form['refresh_interval']['static'] = [
      '#type' => 'number',
      '#title' => $this->t('Static lifetime'),
      '#description' => $this->t('How long static avatars are cached. Leave empty to never delete. Only applies to avatars which are not the users preference.'),
      '#default_value' => $intervals['static'],
      '#step' => 60,
      '#min' => -1,
    ];
    /* *  Keep unused avatars on file system (will use up more disk space)
          Avatars must be purged manually if you change this settings.
       *  Expire unused static avatars (will use up more network bandwidth) */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ak_preview_storage = \Drupal::entityManager()->getStorage('ak_preview');
    $config = $this->config('ak.settings');

    $generators = $form_state->getValue('avatar_generator');
    $generators_old = $config->get('avatar_generator');

    if ($generators_old['default'] != $generators['default']) {
      $ids = $ak_preview_storage
        ->getQuery()
        ->condition('scope', AvatarPreviewInterface::SCOPE_SITE_DEFAULT, '=')
        ->execute();
      $ak_preview_storage->delete($ak_preview_storage->loadMultiple($ids));
    }
    if ($generators_old['fallback'] != $generators['fallback']) {
      $ids = $ak_preview_storage
        ->getQuery()
        ->condition('scope', AvatarPreviewInterface::SCOPE_SITE_FALLBACK, '=')
        ->execute();
      $ak_preview_storage->delete($ak_preview_storage->loadMultiple($ids));
    }

    $config->set('avatar_generator', [
      'default' => $generators['default'],
      'fallback' => $generators['fallback'],
    ]);

    $intervals = $form_state->getValue('refresh_interval');
    $config->set('refresh_interval', [
      'dynamic' => $intervals['dynamic'],
      'static' => $intervals['static'],
    ]);
    $config->save();

    drupal_set_message(t('Settings saved. You may need to clear cache before avatars take effect.'));
  }

}
