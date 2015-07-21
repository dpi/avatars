<?php

/**
 * @file
 * Contains \Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorPluginInterface.
 */

namespace Drupal\avatars\Plugin\AvatarGenerator;

use Drupal\Core\Session\AccountInterface;

/**
 * Interface for AvatarGenerator plugins.
 */
interface AvatarGeneratorPluginInterface {

  /**
   * Gets File object for an avatar.
   *
   * @param AccountInterface $account
   *   A user account.
   *
   * @return \Drupal\file\FileInterface
   *   A file object.
   */
  public function getFile(AccountInterface $account);

  /**
   * Creates a URI to an avatar.
   *
   * @param AccountInterface $account
   *   A user account.
   *
   * @return string
   *   URI to an image file.
   */
  public function generateUri(AccountInterface $account);

}
