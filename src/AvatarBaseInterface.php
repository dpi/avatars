<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarBaseInterface.
 */

namespace Drupal\avatars;

/**
 * Interface for Avatar APIs.
 */
interface AvatarBaseInterface {

  /**
   * Gets the request host name.
   *
   * @return string
   *   A host name.
   */
  public function getHostName();

  /**
   * Sets the request host name.
   *
   * @param string|NULL $hostname
   *   A host name, or NULL to reset to default.
   *
   * @return \Drupal\avatars\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setHostName($hostname = NULL);

  /**
   * Gets the identifier.
   *
   * @return string
   *   The identifier.
   */
  public function getIdentifier();

  /**
   * Sets a unique identifier to be passed to the API.
   *
   * @param string $identifier
   *   A unique identifier, such as an e-mail address.
   * @param bool $pre_hashed
   *   Whether the ID has been pre-obfuscated, otherwise it will happen when the
   *   URL is generated.
   *
   * @throws \Drupal\avatars\Exception\AvatarException
   *   Thrown if the identifier is malformed.
   *
   * @return \Drupal\avatars\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setIdentifier($identifier, $pre_hashed = FALSE);

  /**
   * Determines if the set identifier was prehashed.
   *
   * @return bool|NULL
   *   boolean if identifier has been set, otherwise NULL.
   */
  public function identifierIsPreHashed();

  /**
   * Gets list of avatar types provided by this API.
   *
   * @return string[]
   *   An array of type labels, keyed by type.
   */
  static public function getTypes();

  /**
   * Gets the avatar type.
   */
  public function getType();

  /**
   * Sets the avatar type.
   *
   * @param string $type
   *   The avatar type.
   *
   * @throws \Drupal\avatars\Exception\AvatarException
   *   Thrown if this the type is not defined.
   *
   * @return \Drupal\avatars\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   *
   * @see \Drupal\avatars\AvatarBaseInterface::getTypes()
   */
  public function setType($type);

  /**
   * Sets dimensions to get form the endpoint.
   *
   * @param int $width
   *   The width of the avatar.
   * @param int|NULL $height
   *   The height of the avatar, or NULL to mirror value for width.
   *
   * @throws \Drupal\avatars\Exception\AvatarException
   *   Thrown if the passed dimensions are invalid.
   *
   * @return \Drupal\avatars\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setDimensions($width, $height = NULL);

  /**
   * Whether the URL will be secure.
   *
   * @return bool
   *   Whether the URL should be secure.
   */
  public function isSecure();

  /**
   * Sets the request to secure.
   *
   * @param bool $secure_request
   *   If the request should be secure.
   *
   * @throws \Drupal\avatars\Exception\AvatarException
   *   Thrown if API does not support the requested secure state.
   *
   * @return \Drupal\avatars\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setIsSecure($secure_request = TRUE);

  /**
   * Prepare an identifier for transmission to a third party.
   *
   * @param string $identifier
   *   An identifier to obfuscate.
   *
   * @return string
   *   The obfuscated identifier.
   */
  public static function hashIdentifier($identifier);

  /**
   * Gets the URL for the avatar.
   *
   * @throws \Drupal\avatars\Exception\AvatarException
   *   Thrown if missing parameters.
   *
   * @return string
   *   A URL for an avatar.
   */
  public function getUrl();

}
