<?php

/**
 * @file
 * Contains \Drupal\avatars_adorable\Plugin\AvatarGenerator\Adorable.
 */

namespace Drupal\avatars_adorable\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_adorable\Adorable as AdorableAPI;

/**
 * Adorable.io avatar generator.
 *
 * @AvatarGenerator(
 *   id = "adorable",
 *   label = @Translation("Adorable"),
 *   description = @Translation("Adorable avatars by adorable.io"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Adorable extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $api = new AdorableAPI();
    return $api
      ->setIdentifier($account->getEmail())
      ->setDimensions(256)
      ->getUrl();
  }

}
