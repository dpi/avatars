<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Plugin\AvatarGenerator\Gravatar.
 */

namespace Drupal\avatars_gravatar\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_gravatar\Gravatar as GravatarAPI;

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

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($this->getIdentifier($account))
      ->setType('gravatar')
      ->setFallbackType('404')
      ->setDimensions(256)
      ->getUrl();
  }

}
