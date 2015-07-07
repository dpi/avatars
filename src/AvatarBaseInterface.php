<?php

/**
 * @file
 * Contains \Drupal\ak\AvatarBaseInterface.
 */

namespace Drupal\ak;

interface AvatarBaseInterface {

  /**
   * Gets the request host name.
   *
   * @return string
   */
  public function getHostName();

  /**
   * Sets the request host name.
   *
   * @param string|NULL $hostname
   *   A host name, or NULL to reset to default.
   *
   * @return \Drupal\ak\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setHostName($hostname = NULL);

  /**
   * Gets the identifier
   *
   * @return string
   *   The identifier.
   */
  function getIdentifier();

  /**
   * Sets a unique identifier to be passed to the API.
   *
   * @param string $identifier
   *   A unique identifier, such as an e-mail address.
   * @param boolean $pre_hashed
   *   Whether the ID has been pre-obfuscated, otherwise it will happen when the
   *   URL is generated.
   *
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if the identifier is malformed.
   *
   * @return \Drupal\ak\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  function setIdentifier($identifier, $pre_hashed = FALSE);

  /**
   * Determines if the set identifier was prehashed.
   *
   * @return boolean|NULL
   *   boolean if identifier has been set, otherwise NULL.
   */
  public function identifierIsPreHashed();

  /**
   * @return array
   *   An array of type labels, keyed by type.
   */
  static public function getTypes();

  public function getType();

  /**
   * @param $type
   *
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if this the type is not defined.
   *
   * @return \Drupal\ak\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setType($type);

  /**
   * Sets dimensions to get form the endpoint
   *
   * @param int $width
   *   The width of the avatar.
   * @param int|NULL $height
   *   The height of the avatar, or NULL to mirror value for width.
   *
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if the passed dimensions are invalid.
   *
   * @return \Drupal\ak\AvatarBaseInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setDimensions($width, $height = NULL);

  /**
   * Whether the URL will be secure.
   *
   * @return boolean
   *   Whether the URL should be secure.
   */
  public function isSecure();

  /**
   * Sets the request to secure.
   *
   * @param boolean $secure_request
   *   If the request should be secure.
   *
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if API does not support the requested secure state.
   *
   * @return \Drupal\ak\AvatarBaseInterface
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
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if missing parameters.
   *
   * @return string
   */
  public function getUrl();

}