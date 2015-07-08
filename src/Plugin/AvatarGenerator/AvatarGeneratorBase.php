<?php

/**
 * @file
 * Contains \Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase.
 */

namespace Drupal\ak\Plugin\AvatarGenerator;

use Drupal\Core\Session\AccountInterface;

/**
 * AvatarGenerator plugin base class.
 */
abstract class AvatarGeneratorBase implements AvatarGeneratorPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getFile(AccountInterface $account) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    return NULL;
  }

}
