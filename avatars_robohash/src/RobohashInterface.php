<?php

/**
 * @file
 * Contains \Drupal\avatars_robohash\RobohashInterface.
 */

namespace Drupal\avatars_robohash;

/**
 * Provides an interface to the Robohash.org API.
 */
interface RobohashInterface {

  /*
   * URL for insecure requests.
   *
   * @var string
   */
  const ROBOHASH_HOSTNAME = 'robohash.org';

  /*
   * Maximum width images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MAXIMUM_WIDTH = 300;

  /*
   * Maximum height images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MAXIMUM_HEIGHT = 300;

  /*
   * Minimum width images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MINIMUM_WIDTH = 1;

  /*
   * Minimum height images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MINIMUM_HEIGHT = 1;

  /**
   * A list of valid backgrounds.
   *
   * @return array
   *   An array of background labels keyed by background.
   */
  public static function getBackgrounds();

  /**
   * Backgrounds mapped to GET values.
   *
   * @return array
   *   An array of GET values keyed by background.
   */
  public static function getBackgroundsMap();

  /**
   * Gets the background.
   *
   * @return string|NULL
   *   The background, or NULL for default.
   */
  public function getBackground();

  /**
   * Set the background.
   *
   * @param string|NULL $background
   *   The background to use, or NULL to use default.
   *
   * @return \Drupal\avatars_robohash\RobohashInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setBackground($background = NULL);

  /**
   * Avatar types mapped to GET values.
   *
   * @return array
   *   An array of GET values keyed by type.
   */
  static public function getTypesMap();

  /**
   * Sets type to random.
   *
   * Will not be random for each request, rather for each identifier.
   *
   * @return \Drupal\avatars_robohash\RobohashInterface
   *   Returns the called Robohash object for chaining.
   */
  public function setTypeRandom();

}
