<?php

/**
 * @file
 * Contains \Drupal\ak\AvatarPreviewInterface.
 */

namespace Drupal\ak;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;

interface AvatarPreviewInterface extends ContentEntityInterface {

  /**
   * Scopes
   *
   * Scopes are used for cleanup operations. Dynamic avatar previews will always
   * expire according to dynamic_lifetime configuration. Only static avatar
   * previews marked with scope: temporary will expire automatically.
   *
   * Scopes exist due to computational complexity resulting from the calculating
   * user x site default avatar generator for each user.
   *
   * If a preference on any of these levels changes, then the avatar preview
   * will be expired.
   *
   * @see \Drupal\ak\AvatarManager::syncAvatar
   */

  /**
   * Keep avatar preview temporarily.
   *
   * @var int
   *
   * @see ak_cron().
   */
  const SCOPE_TEMPORARY = 0;

  /**
   * Whether the avatar preview was generated due to a an individual users
   * preference.
   *
   * If the user changes its' avatar generator preference, the preview will be
   * purged immediately.
   *
   * @var int
   */
  const SCOPE_USER_SELECTED = 1;

  /**
   * Whether the avatar preview was generated due to the user not having
   * selected its own avatar generator.
   *
   * If the site default avatar avatar generator preference changes, all
   * previews with this scope will be purged immediately.
   *
   * @var int
   */
  const SCOPE_SITE_DEFAULT = 2;

  /**
   * Whether the avatar preview was generated due to the user selected or site
   * site default generator failing.
   *
   * If the site fallback avatar avatar generator preference changes, all
   * previews with this scope will be purged immediately.
   *
   * @var int
   */
  const SCOPE_SITE_FALLBACK = 3;

  /**
   * Get avatar generator plugin ID.
   *
   * @return string
   */
  function getAvatarGenerator();

  /**
   * Set avatar generator plugin ID.
   *
   * @param string $avatar_generator
   *   An avatar generator plugin ID.
   *
   * @return \Drupal\ak\AvatarPreviewInterface
   *   Return avatar preview for chaining.
   */
  function setAvatarGenerator($avatar_generator);

  /**
   * Get associated user.
   *
   * @return \Drupal\user\UserInterface
   *   A user entity.
   */
  public function getUser();

  /**
   * Set associated user.
   *
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   *
   * @return \Drupal\ak\AvatarPreviewInterface
   *   Return avatar preview for chaining.
   */
  public function setUser(UserInterface $user);

  /**
   * Get associated avatar file.
   *
   * @return \Drupal\file\FileInterface|NULL
   *   A file entity.
   */
  public function getAvatar();

  /**
   * Set associated avatar file.
   *
   * @param \Drupal\file\FileInterface|NULL $file
   *   A file entity, or NULL if the generator did not create an avatar.
   *
   * @return \Drupal\ak\AvatarPreviewInterface
   *   Return avatar preview for chaining.
   */
  public function setAvatar(FileInterface $file = NULL);

  /**
   * Gets the creation time of the avatar preview.
   *
   * @return int
   *   Timestamp of the creation date.
   */
  function getCreatedTime();

  /**
   * Sets the creation time of the avatar preview.
   *
   * @param int $timestamp
   *   Timestamp of the creation date.
   *
   * @return \Drupal\ak\AvatarPreviewInterface
   *   Return avatar preview for chaining.
   */
  function setCreatedTime($timestamp);

  /**
   * Gets the scope of the avatar preview.
   *
   * @return int
   *   Value of a \Drupal\ak\AvatarPreviewInterface::SCOPE_* constant.
   */
  function getScope();

  /**
   * Sets the scope of the avatar preview.
   *
   * @param int $scope
   *   Value of a \Drupal\ak\AvatarPreviewInterface::SCOPE_* constant.
   *
   * @return \Drupal\ak\AvatarPreviewInterface
   *   Return avatar preview for chaining.
   */
  function setScope($scope);

  /**
   * Queries for an avatar preview and loads it.
   *
   * @param string $avatar_generator
   *   An avatar generator plugin ID.
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   *
   * @return \Drupal\ak\AvatarPreviewInterface
   *   An avatar preview entity.
   */
  public static function getAvatarPreview($avatar_generator, UserInterface $user);

}