<?php

/**
 * @file
 * Contains \Drupal\ak_robohash\Plugin\AvatarGenerator\Robohash.
 */

namespace Drupal\ak_robohash\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_robohash\Robohash as RobohashAPI;

/**
 * Robohash robots avatar generator.
 *
 * @AvatarGenerator(
 *   id = "robohash_robots",
 *   label = @Translation("Robots"),
 *   description = @Translation("Robots from Robohash.org"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Robohash extends AvatarGeneratorBase {

  function generateURI(AccountInterface $account){
    $robohash = new RobohashAPI();
    return $robohash
      ->setIdentifier($account->getEmail())
      ->setType('robot')
      ->getUrl();
  }

}
