<?php

/**
 * @file
 * Contains \Drupal\ak_gravatar\GravatarInterface.
 */

namespace Drupal\ak_gravatar;

/**
 * Provides an interface for the Gravatar.com API.
 */
interface GravatarInterface {

  /*
   * URL for insecure requests.
   *
   * @var string
   */
  const GRAVATAR_HOSTNAME = 'gravatar.com';

  /*
   * URL for secure requests.
   *
   * @var string
   */
  const GRAVATAR_HOSTNAME_SECURE = 'secure.gravatar.com';

  /*
   * Minimum width images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MINIMUM_WIDTH = 1;

  /*
   * Maximum width images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MAXIMUM_WIDTH = 2048;

  /**
   * Avatar types mapped to 'd' GET values.
   *
   * @return array
   *   An array of GET values keyed by type.
   */
  static public function getTypesMap();

  /**
   * Valid fallback types for when 'gravatar' is the primary type.
   *
   * @return string[]
   *   An array of fallback avatar types.
   */
  static public function getFallbackTypes();

  /**
   * Get the fallback avatar type.
   *
   * @return string|NULL
   *   The fallback avatar type, or NULL to use default.
   */
  public function getFallbackType();

  /**
   * The type used for when 'gravatar' type fails.
   *
   * Such as when there is no Gravatar for the hash.
   *
   * @param string|NULL $type
   *   An avatar type.
   *
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if the passed type is invalid.
   *
   * @return \Drupal\ak_gravatar\GravatarInterface
   *   Returns the called object for chaining.
   */
  public function setFallbackType($type = NULL);

  /**
   * The URI to an image used for when 'gravatar' type fails.
   *
   * Such as when there is no Gravatar for the hash.
   *
   * @param string $uri
   *   The URI of an image to use as a fallback.
   *
   * @return \Drupal\ak_gravatar\GravatarInterface
   *   Returns the called object for chaining.
   */
  public function setFallbackUri($uri);

  /**
   * Get a list of valid ratings.
   *
   * @return array
   *   An array of rating labels keyed by rating.
   */
  static public function getRatings();

  /**
   * Get the rating.
   *
   * @return string|NULL
   *   The set rating, or NULL if no rating.
   */
  public function getRating();

  /**
   * Sets the maximum gravatar rating.
   *
   * @param string|NULL $rating
   *   The rating to set, or NULL if no rating.
   *
   * @throws \Drupal\ak\Exception\AvatarException
   *   Thrown if the rating is invalid.
   *
   * @return \Drupal\ak_gravatar\GravatarInterface
   *   Returns the called object for chaining.
   */
  public function setRating($rating = NULL);

}
