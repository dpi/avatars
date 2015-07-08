<?php

/**
 * @file
 * Contains \Drupal\ak_robohash\Plugin\AvatarGenerator\RobohashHeads.
 */

namespace Drupal\ak_robohash\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_robohash\Robohash as RobohashAPI;

/**
 * Robohash heads avatar generator.
 *
 * @AvatarGenerator(
 *   id = "robohash_robot_heads",
 *   label = @Translation("Robot heads"),
 *   description = @Translation("Robot heads from Robohash.org"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class RobohashHeads extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $robohash = new RobohashAPI();
    return $robohash
      ->setIdentifier($account->getEmail())
      ->setType('robot_head')
      ->getUrl();
  }

}
