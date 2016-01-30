<?php

/**
 * @file
 * Contains \Drupal\avatars_robohash\Robohash.
 */

namespace Drupal\avatars_robohash;

use Drupal\avatars\Exception\AvatarException;
use Drupal\avatars\AvatarBase;

/**
 * Implements the Robohash.org API.
 */
class Robohash extends AvatarBase implements RobohashInterface {

  /*
   * The background to use, or NULL to use default.
   *
   * @var string|NULL
   */
  protected $background;

  /**
   * Constructs a new Robohash object.
   */
  public function __construct() {
    $this->setDimensionConstraints(
      Robohash::DIMENSION_MINIMUM_WIDTH,
      Robohash::DIMENSION_MAXIMUM_WIDTH,
      Robohash::DIMENSION_MINIMUM_HEIGHT,
      Robohash::DIMENSION_MAXIMUM_HEIGHT
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getHostName() {
    $hostname = parent::getHostName();
    return isset($hostname) ? $hostname : $this::ROBOHASH_HOSTNAME;
  }

  /**
   * {@inheritdoc}
   */
  public static function getBackgrounds() {
    return [
      'transparent' => 'Transparent',
      'places' => 'Places',
      'patterns' => 'Patterns',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getBackgroundsMap() {
    return [
      'transparent' => '',
      'places' => 'bg1',
      'patterns' => 'bg2',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getBackground() {
    return $this->background;
  }

  /**
   * {@inheritdoc}
   */
  public function setBackground($background = NULL) {
    if (!array_key_exists($background, $this->getBackgrounds())) {
      throw new AvatarException('Invalid background');
    }
    $this->background = $background;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  static public function getTypes() {
    return [
      'robot' => 'Robot',
      'monster' => 'Monster',
      'robot_head' => 'Robot Head',
    ];
  }

  /**
   * {@inheritdoc}
   */
  static public function getTypesMap() {
    return [
      'robot' => 1,
      'monster' => 2,
      'robot_head' => 3,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setTypeRandom() {
    $this->type = 'any';
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    $kv = [];
    $url = ($this->isSecure() ? 'https://' : 'http://') . $this->getHostName() . '/';

    $identifier = $this->getIdentifier();
    if (!strlen($identifier)) {
      throw new AvatarException('Robohash missing identifier/hash');
    }

    $url .= !$this->identifierIsPreHashed() ? $this->hashIdentifier($identifier) : $url;

    $background = $this->getBackground();
    $background_map = $this->getBackgroundsMap();
    if (!empty($background) && ($background != key($background_map))) {
      $kv['bgset'] = $background_map[$background];
    }

    $type_map = $this->getTypesMap();
    $type = $this->getType();
    if ($type == 'any') {
      $kv['set'] = 'any';
    }
    elseif (!empty($type) && ($type != key($type_map))) {
      $kv['set'] = 'set' . $type_map[$type];
    }

    $width = $this->width;
    $height = $this->height;
    // Robohash requires width AND height to be set.
    // Robohash dimensions do not have to be square, although the rendered image
    // will be distorted.
    // Validation is done in setDimensions method.
    if (is_numeric($width) && is_numeric($height)) {
      $kv['size'] = $width . 'x' . $height;
    }

    $query = http_build_query($kv);
    return !empty($query) ? $url . '?' . $query : $url;
  }

}
