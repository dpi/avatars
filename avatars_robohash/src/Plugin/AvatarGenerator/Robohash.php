<?php

/**
 * @file
 * Contains \Drupal\avatars_robohash\Plugin\AvatarGenerator\Robohash.
 */

namespace Drupal\avatars_robohash\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_robohash\Robohash as RobohashAPI;
use Drupal\Core\Form\FormStateInterface;

/**
 * Robohash avatar generator.
 *
 * @AvatarGenerator(
 *   id = "robohash",
 *   label = @Translation("Robohash"),
 *   description = @Translation("Robots and monsters from Robohash.org"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Robohash extends AvatarGeneratorBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'type' => '',
      'background' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $robohash = new RobohashAPI();

    $type_map = [
      'robots' => 'robot',
      'robot_heads' => 'robot_head',
      'monsters' => 'monster',
    ];
    $type = $this->configuration['type'];

    $background_map = [
      'transparent' => 'transparent',
      'background_1' => 'places',
      'background_2' => 'patterns',
    ];
    $background = $this->configuration['background'];

    return $robohash
      ->setIdentifier($this->getIdentifier($account))
      ->setType(isset($type_map[$type]) ? $type_map[$type] : '')
      ->setBackground(isset($background_map[$background]) ? $background_map[$background] : '')
      ->getUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Type'),
      '#options' => [
        'robots' => $this->t('Robots'),
        'robot_heads' => $this->t('Robot Heads'),
        'monsters' => $this->t('Monsters'),
      ],
      '#default_value' => $this->configuration['type'],
    ];
    $form['background'] = [
      '#type' => 'radios',
      '#title' => $this->t('Background'),
      '#options' => [
        'transparent' => $this->t('Transparent'),
        'background_1' => $this->t('Places'),
        'background_2' => $this->t('Patterns'),
      ],
      '#empty_value' => '',
      '#default_value' => $this->configuration['background'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['type'] = $form_state->getValue('type');
    $this->configuration['background'] = $form_state->getValue('background');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if (empty($this->configuration['type']) || empty($this->configuration['background'])) {
      $summary[] = $this->t('Missing Configuration');
    }
    else {
      $summary[]['#markup'] = $this->t('Type: @type | Background: @background' , [
        '@type' => $this->configuration['type'],
        '@background' => $this->configuration['background'],
      ]);
    }
    return $summary;
  }

}
