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
use Drupal\Core\Render\Element;
use Drupal\avatars\AvatarPreviewInterface;

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
      'enabled' => [
        'data' => $this->t('Enabled'),
        'class' => ['checkbox'],
      ],
      'weight' => $this->t('Weight'),
      'type' => $this->t('Type'),
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
      '#attributes' => array(
        'id' => 'avatar-generators',
      ),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'generator-weight',
        ],
      ],
    ];

    // Add non-fallback generators.
    $fallback_options = [];
    foreach ($this->avatarGenerator->getDefinitions() as $plugin_id => $definition) {
      if ($plugin_id != 'broken') {
        if (empty($definition['fallback'])) {
          $row = [];
          $row['label']['#markup'] = $definition['label'];
          $row['enabled'] = [];
          $row['weight'] = [];
          $row['type']['#markup'] = $definition['dynamic'] ? $this->t('Dynamic') : $this->t('Static');
          $avatar_generators[$plugin_id] = $row;
        }
        else {
          $fallback_options[$plugin_id] = $definition['label'];
        }
      }
    }

    // User preference computed avatar generator.
    $avatar_generators['_user_preference'] = [
      'label' => ['#markup' => $this->t('<em>User preference</em>')],
      'enabled' => [],
      'weight' => [],
      'type' => ['#markup' => $this->t('Any')],
    ];

    // Add fallback generator.
    $avatar_generators['_fallback'] = [
      'label' => ['#markup' => 'Final fallback'], //@todo remove
      'enabled' => [],
      'weight' => [],
      'type' => ['#markup' => $this->t('Any')],
    ];
    $avatar_generators['_fallback']['label'] = [
      '#type' => 'select',
      '#title' => $this->t('Final fallback'),
      '#title_display' => 'invisible',
      '#description' => $this->t('These avatar generators are guaranteed to produce an avatar. Any avatar generators below this will not run.'),
      '#options' => $fallback_options,
    ];

    // Add enabled and weights to all preferences.
    foreach (Element::children($avatar_generators) as $plugin_id) {
      $avatar_generators[$plugin_id]['#attributes']['class'][] = 'draggable';
      $avatar_generators[$plugin_id]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enabled'),
        '#title_display' => 'invisible',
        '#default_value' => FALSE,
        '#wrapper_attributes' => [
          'class' => [
            'checkbox',
          ],
        ]
      ];
      $avatar_generators[$plugin_id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @label', array('@label' => $plugin_id)),
        '#title_display' => 'invisible',
        '#default_value' => NULL,
        '#attributes' => [
          'class' => ['generator-weight']
        ]
      ];
    }

    // re-sort rows based on config.
    $i = 0;
    foreach ($config->get('avatar_generators') as $generator) {
      if (isset($avatar_generators[$generator])) {
        $form['avatar_generators'][$generator] = $avatar_generators[$generator];
        $form['avatar_generators'][$generator]['enabled']['#default_value'] = TRUE;
        $form['avatar_generators'][$generator]['weight']['#default_value'] = $i;
        unset($avatar_generators[$generator]);
      }
      // could be a fallback.
      else if (isset($fallback_options[$generator])) {
        $avatar_generators['_fallback']['label']['#default_value'] = $generator;
        $form['avatar_generators']['_fallback'] = $avatar_generators['_fallback'];
        $form['avatar_generators']['_fallback']['enabled']['#default_value'] = TRUE;
        $form['avatar_generators']['_fallback']['weight']['#default_value'] = $i;
        unset($avatar_generators['_fallback']);
      }
      $i++;
    }

    // add the rest
    foreach ($avatar_generators as $plugin_id => $generator) {
      $form['avatar_generators'][$plugin_id] = $generator;
      $form['avatar_generators'][$plugin_id]['weight']['#default_value'] = $i;
      $i++;
    }


    $image_styles = [];
    $form['image_style'] = [
      '#type' => 'select',
      '#options' => $image_styles,
      '#title' => $this->t('Preference form image style'),
      '#description' => $this->t('Select image style to use in the users avatar generator preference form.'),
      '#access' => FALSE,
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
      '#field_suffix' => $this->t('seconds'),
    ];

    /*
     * Keep unused avatars on file system (will use up more disk space)
     * Avatars must be purged manually if you change this settings.
     * Expire unused static avatars (will use up more network bandwidth)
     * */
    $form['refresh_interval']['static'] = [
      '#type' => 'number',
      '#title' => $this->t('Static lifetime'),
      '#description' => $this->t('How long static avatars are cached. Leave empty to never delete. Only applies to avatars which are not the users preference.'),
      '#default_value' => $intervals['static'],
      '#step' => 60,
      '#min' => -1,
      '#field_suffix' => $this->t('seconds'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $avatars_preview_storage = \Drupal::entityManager()->getStorage('avatars_preview');
    $config = $this->config('avatars.settings');
    $generators_original = $config->get('avatar_generators');

    $generators = [];
    // Generators are already sorted correctly.
    foreach ($form_state->getValue('avatar_generators') as $generator_id => $row) {
      if (!empty($row['enabled'])) {
        if ($generator_id == '_fallback') {
          $generator_id = $row['label'];
        }
        $generators[] = $generator_id;
      }
    }
    $config->set('avatar_generators', $generators);

    // If fallback changed, then purge fallback previews.
    if ($generators_original != $generators) {
      $ids = $avatars_preview_storage
        ->getQuery()
        ->condition('scope', AvatarPreviewInterface::SCOPE_SITE_FALLBACK, '=')
        ->execute();
      $avatars_preview_storage->delete($avatars_preview_storage->loadMultiple($ids));
    }

    $intervals = $form_state->getValue('refresh_interval');
    $config->set('refresh_interval', [
      'dynamic' => $intervals['dynamic'],
      'static' => $intervals['static'],
    ]);
    $config->save();

    drupal_set_message(t('Settings saved. You may need to clear cache before avatars tavatarse effect.'));
  }

}
