<?php

/**
 * @file
 * Contains \Drupal\avatars\Plugin\AvatarGenerator\Broken.
 */

namespace Drupal\avatars\Plugin\AvatarGenerator;

use Drupal\Core\Session\AccountInterface;

/**
 * Fallback plugin for missing AvatarGenerator plugins.
 *
 * @AvatarGenerator(
 *   id = "broken",
 *   label = @Translation("Broken/Missing")
 * )
 */
class Broken extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    return '';
  }

}
