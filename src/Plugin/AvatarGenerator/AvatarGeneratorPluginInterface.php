<?php

/**
 * @file
 * Contains \Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorPluginInterface.
 */

namespace Drupal\ak\Plugin\AvatarGenerator;

use Drupal\Core\Session\AccountInterface;

/**
 * Interface for AvatarGenerator plugins.
 */
interface AvatarGeneratorPluginInterface {

  /**
   * @param AccountInterface $account
   *
   * @return \Drupal\file\FileInterface
   */
  function getFile(AccountInterface $account);

  /**
   * @param AccountInterface $account
   *
   * @return string
   */
  function generateURI(AccountInterface $account);

}