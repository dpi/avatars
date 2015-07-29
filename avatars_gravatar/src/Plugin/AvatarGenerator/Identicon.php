<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Plugin\AvatarGenerator\Identicon.
 */

namespace Drupal\avatars_gravatar\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_gravatar\Gravatar as GravatarAPI;

/**
 * Identicon avatar generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar_identicon",
 *   label = @Translation("Identicon"),
 *   description = @Translation("Identicon from Gravatar.com"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Identicon extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($this->getIdentifier($account))
      ->setType('identicon')
      ->setDimensions(256)
      ->getUrl();
  }

}
