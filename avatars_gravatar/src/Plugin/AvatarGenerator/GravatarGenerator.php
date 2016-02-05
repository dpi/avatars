<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Plugin\AvatarGenerator\Identicon.
 */

namespace Drupal\avatars_gravatar\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_gravatar\Gravatar as GravatarAPI;

/**
 * Gravatar generated avatars generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar_generator",
 *   label = @Translation("Gravatar generator"),
 *   description = @Translation("Identicon, MonsterID, Retro, and Wavatar avatar generators."),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class GravatarGenerator extends AvatarGeneratorBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'type' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($this->getIdentifier($account))
      ->setType($this->configuration['type'])
      ->setDimensions(256)
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
        'identicon' => $this->t('Identicon'),
        'monsterid' => $this->t('Monster ID'),
        'wavatar' => $this->t('Wavatar'),
        'retro' => $this->t('8-bit style avatar'),
      ],
      '#default_value' => $this->configuration['type'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['type'] = $form_state->getValue('type');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if (empty($this->configuration['type'])) {
      $summary[] = $this->t('Missing Configuration');
    }
    else {
      $summary[]['#markup'] = $this->t('Type: @type' , [
        '@type' => $this->configuration['type'],
      ]);
    }
    return $summary;
  }
}
