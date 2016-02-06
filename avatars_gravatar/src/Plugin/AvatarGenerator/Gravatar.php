<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Plugin\AvatarGenerator\Gravatar.
 */

namespace Drupal\avatars_gravatar\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_gravatar\Gravatar as GravatarAPI;
use Drupal\Core\Form\FormStateInterface;

/**
 * Gravatar avatar generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar",
 *   label = @Translation("Gravatar"),
 *   description = @Translation("Universal avatar uploaded to Gravatar.com"),
 *   fallback = FALSE,
 *   dynamic = TRUE,
 *   remote = TRUE
 * )
 */
class Gravatar extends AvatarGeneratorBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'maximum_rating' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();

    if (!empty($this->configuration['maximum_rating'])) {
      $gravatar->setRating($this->configuration['maximum_rating']);
    }

    return $gravatar
      ->setIdentifier($this->getIdentifier($account))
      ->setType('gravatar')
      ->setFallbackType('404')
      ->setDimensions(256)
      ->getUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['maximum_rating'] = [
      '#type' => 'radios',
      '#title' => $this->t('Maximum content rating'),
      '#options' => [
        '' => $this->t('Unrestricted'),
        'g' => $this->t('G: suitable for display on all websites with any audience type.'),
        'pg' => $this->t('PG: may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence.'),
        'r' => $this->t('R: may contain such things as harsh profanity, intense violence, nudity, or hard drug use.'),
        'x' => $this->t('X: may contain hardcore sexual imagery or extremely disturbing violence.'),
      ],
      '#required' => TRUE,
      '#default_value' => $this->configuration['maximum_rating'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['maximum_rating'] = $form_state->getValue('maximum_rating');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if (!empty($this->configuration['maximum_rating'])) {
      $summary[]['#markup'] = $this->t('Maximum rating: @maximum_rating', [
        '@maximum_rating' => $this->configuration['maximum_rating'],
      ]);
    }
    return $summary;
  }

}
