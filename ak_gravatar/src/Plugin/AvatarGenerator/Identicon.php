<?php

/**
 * @file
 * Contains \Drupal\ak_gravatar\Plugin\AvatarGenerator\Identicon.
 */

namespace Drupal\ak_gravatar\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_gravatar\Gravatar as GravatarAPI;

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
      ->setIdentifier($account->getEmail())
      ->setType('identicon')
      ->setDimensions(256)
      ->getUrl();
  }

}
