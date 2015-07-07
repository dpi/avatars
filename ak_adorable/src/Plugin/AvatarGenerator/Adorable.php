<?php

/**
 * @file
 * Contains \Drupal\ak_adorable\Plugin\AvatarGenerator\Adorable.
 */

namespace Drupal\ak_adorable\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_adorable\Adorable as AdorableAPI;

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

  function generateURI(AccountInterface $account){
    $api = new AdorableAPI();
    return $api
      ->setIdentifier($account->getEmail())
      ->setDimensions(256)
      ->getUrl();
  }

}
