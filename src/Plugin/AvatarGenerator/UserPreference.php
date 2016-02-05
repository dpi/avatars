<?php

/**
 * @file
 * Contains \Drupal\avatars\Plugin\AvatarGenerator\UserPreference.
 */

namespace Drupal\avatars\Plugin\AvatarGenerator;

use Drupal\Core\Session\AccountInterface;

/**
 * User preference plugin.
 *
 * This is a special plugin.
 *
 * @AvatarGenerator(
 *   id = "user_preference",
 *   label = @Translation("User preference"),
 *   description = @Translation("Avatar generator calculated based on user preference."),
 *   dynamic = TRUE,
 *   fallback = FALSE,
 *   remote = TRUE
 * )
 */
class UserPreference extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    // This is a special plugin. This method should never be called.
    throw new \Exception(__FUNCTION__ . ' called for user_preference plugin.');
  }

}
