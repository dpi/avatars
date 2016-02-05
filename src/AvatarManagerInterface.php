<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarManagerInterface.
 */

namespace Drupal\avatars;

use Drupal\user\UserInterface;

/**
 * Provides an interface to the avatar manager service.
 */
interface AvatarManagerInterface {

  /**
   * Check user avatar for changes, and inserts the avatar into the user entity.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   */
  public function syncAvatar(UserInterface $user);

  /**
   * Go down the the avatar generator preference hierarchy for a user, loading
   * each avatar until a valid avatar is found.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface|NULL
   *   An avatar preview entity.
   */
  function findValidAvatar(UserInterface $user);

  /**
   * Create avatar if it does not exist.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   * @param \Drupal\avatars\AvatarGeneratorInterface $avatar_generator
   *   An avatar generator instance.
   * @param int $scope
   *   Caching scope level.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface|FALSE
   *   An avatar preview entity.
   */
  public function refreshAvatarGenerator(UserInterface $user, AvatarGeneratorInterface $avatar_generator, $scope);

  /**
   * Downloads all avatar previews for a user.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface[]
   *   An array of refreshed avatar preview entities.
   */
  function refreshAllAvatars(UserInterface $user);

  /**
   * Download avatar and insert it into a file.
   *
   * Ignores any existing caches. Use refreshAvatarGenerator to take advantage
   * of internal caching.
   *
   * @param \Drupal\avatars\AvatarGeneratorInterface $avatar_generator
   *   An avatar generator instance.
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\file\FileInterface|FALSE
   */
  function getAvatarFile(AvatarGeneratorInterface $avatar_generator, UserInterface $user);

  /**
   * Avatar preference generators.
   *
   * Ordered by priority.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Generator
   *   Generator yield pairs:
   *   key: string $avatar_generator_machine_name
   *   value: value of constants prefixed with AvatarPreviewInterface::SCOPE_*
   */
  public function getPreferences(UserInterface $user);

  /**
   * Invalidate any cache where the user avatar is displayed.
   *
   * Call if the avatar has changed, or is expected to change.
   *
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   */
  function invalidateUserAvatar(UserInterface $user);

  /**
   * Triggers expected change for dynamic avatar generator.
   *
   * @param \Drupal\avatars\AvatarGeneratorInterface $avatar_generator
   *   An avatar generator instance.
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   */
  function notifyDynamicChange(AvatarGeneratorInterface $avatar_generator, UserInterface $user);

}
