<?php

/**
 * @file
 * Contains \Drupal\avatars_robohash\Plugin\AvatarGenerator\RobohashMonsters.
 */

namespace Drupal\avatars_robohash\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_robohash\Robohash as RobohashAPI;

/**
 * Robohash monsters avatar generator.
 *
 * @AvatarGenerator(
 *   id = "robohash_monsters",
 *   label = @Translation("Monsters"),
 *   description = @Translation("Monsters from Robohash.org"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class RobohashMonsters extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $robohash = new RobohashAPI();
    return $robohash
      ->setIdentifier($account->getEmail())
      ->setType('monster')
      ->getUrl();
  }

}
