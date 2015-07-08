<?php

/**
 * @file
 * Contains \Drupal\ak_gravatar\Plugin\AvatarGenerator\Wavatar.
 */

namespace Drupal\ak_gravatar\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_gravatar\Gravatar as GravatarAPI;

/**
 * Wavatar avatar generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar_wavatar",
 *   label = @Translation("Wavatar"),
 *   description = @Translation("Wavatar from Gravatar.com"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Wavatar extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($account->getEmail())
      ->setType('wavatar')
      ->setDimensions(256)
      ->getUrl();
  }

}
