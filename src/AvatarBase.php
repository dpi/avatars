<?php

/**
 * @file
 * Contains \Drupal\ak\AvatarBase.
 */

namespace Drupal\ak;

use Drupal\ak\Exception\AvatarException;

abstract class AvatarBase implements AvatarBaseInterface {

  protected $hostname;
  protected $type;
  protected $identifier;
  protected $secure;
  protected $prehashed;
  protected $width;
  protected $height;

  /*
   * Maximum width of the avatar.
   *
   * @var int
   */
  protected $dimension_width_maximum;

  /*
   * Minimum width of the avatar.
   *
   * @var int
   */
  protected $dimension_width_minimum;

  /*
   * Maximum height of the avatar.
   *
   * @var int
   */
  protected $dimension_height_maximum;

  /*
   * Minimum height of the avatar.
   *
   * @var int
   */
  protected $dimension_height_minimum;


  /**
   * {@inheritdoc}
   */
  public function getHostName() {
    return $this->hostname;
  }

  /**
   * {@inheritdoc}
   */
  public function setHostName($hostname = NULL) {
    $this->hostname = $hostname;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function setType($type) {
    if (!array_key_exists($type, $this->getTypes())) {
      throw new AvatarException('Invalid type');
    }
    $this->type = $type;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function getIdentifier() {
    return $this->identifier;
  }

  /**
   * {@inheritdoc}
   */
  function identifierIsPreHashed() {
    return $this->prehashed;
  }

  /**
   * {@inheritdoc}
   */
  function setIdentifier($identifier, $pre_hashed = FALSE) {
    if (!is_scalar($identifier)) {
      throw new AvatarException('Invalid identifier');
    }
    $this->identifier = $identifier;
    $this->prehashed = $pre_hashed;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isSecure() {
    return $this->secure;
  }

  /**
   * {@inheritdoc}
   */
  public function setIsSecure($secure_request = TRUE) {
    $this->secure = $secure_request;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setDimensions($width, $height = NULL) {
    if ($this->dimension_width_maximum && ($width > $this->dimension_width_maximum)) {
      throw new AvatarException('Avatar width is too large.');
    }
    if ($this->dimension_width_minimum && ($width < $this->dimension_width_minimum)) {
      throw new AvatarException('Avatar width is too small.');
    }
    if ($this->dimension_height_maximum && ($height > $this->dimension_height_maximum)) {
      throw new AvatarException('Avatar height is too large.');
    }
    if ($this->dimension_height_minimum && ($height < $this->dimension_height_minimum)) {
      throw new AvatarException('Avatar height is too small.');
    }
    $this->width = $width;
    $this->height = ($height === NULL) ? $this->width : $height;
    return $this;
  }

  /**
   * Sets constraints for avatar dimensions
   *
   * @param int $width_minimum
   *   The minimum width.
   * @param int $width_maximum
   *   The maximum width.
   * @param int|null $height_minimum
   *   The minimum height, or NULL to mirror minimum width.
   * @param int|null $height_maximum
   *   The maximum height, or NULL to mirror maximum width.
   */
  protected function setDimensionConstraints($width_minimum, $width_maximum, $height_minimum = NULL, $height_maximum = NULL) {
    $this->dimension_width_maximum = $width_maximum;
    $this->dimension_width_minimum = $width_minimum;
    $this->dimension_height_minimum = $height_minimum;
    $this->dimension_height_maximum = $height_maximum;
  }

  /**
   * {@inheritdoc}
   */
  public static function hashIdentifier($identifier) {
    return md5($identifier);
  }

}