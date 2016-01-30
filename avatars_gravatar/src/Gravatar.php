<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Gravatar.
 */

namespace Drupal\avatars_gravatar;

use Drupal\avatars\AvatarBase;
use Drupal\avatars\Exception\AvatarException;

/**
 * Implements the Gravatar.com API.
 */
class Gravatar extends AvatarBase implements GravatarInterface {

  /*
   * The type that should be used if the main type is 'gravatar', and there is
   * no Gravatar for the hash.
   *
   * @var string|NULL
   */
  protected $fallbackType;

  /*
   * The URI to an image that should be used if the main type is 'gravatar', and
   * there is no Gravatar for the hash.
   *
   * @var string|NULL
   */
  protected $fallbackURI;

  /**
   * Maximum censorship rating for the image when main type is 'gravatar'.
   *
   * Endpoint will use fallback image if the gravatar exceeds this rating.
   *
   * Set to NULL if no rating is required.
   *
   * @var string|NULL
   */
  protected $rating;

  /**
   * Constructs a new Gravatar object.
   */
  public function __construct() {
    $this->fallbackType = '404';
    $this->setDimensionConstraints(
      GravatarInterface::DIMENSION_MINIMUM_WIDTH,
      GravatarInterface::DIMENSION_MAXIMUM_WIDTH
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getHostName() {
    $hostname = parent::getHostName();
    return isset($hostname) ? $hostname : ($this->isSecure() ? static::GRAVATAR_HOSTNAME_SECURE : static::GRAVATAR_HOSTNAME);
  }

  /**
   * {@inheritdoc}
   */
  static public function getTypes() {
    return [
      'gravatar' => 'Gravatar',
      'mysteryman' => 'Mystery Man',
      'identicon' => 'Identicon',
      'monsterid' => 'Monsterid',
      'wavatar' => 'Wavatar',
      'retro' => 'Retro',
      'blank' => 'Blank',
    ];
  }

  /**
   * {@inheritdoc}
   */
  static public function getTypesMap() {
    return [
      'gravatar' => 'gravatar',
      'mysteryman' => 'mysteryman',
      'identicon' => 'identicon',
      'monsterid' => 'monsterid',
      'wavatar' => 'wavatar',
      'retro' => 'retro',
      'blank' => 'blank',
      '404' => 404,
    ];
  }

  /**
   * {@inheritdoc}
   */
  static public function getFallbackTypes() {
    return array_diff(
      array_keys(Gravatar::getTypesMap()),
      ['gravatar']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackType() {
    return $this->fallbackType;
  }

  /**
   * {@inheritdoc}
   */
  public function setFallbackType($type = NULL) {
    // Fallback type only applies when primary type is set to 'gravatar'.
    if (!in_array($type, $this->getFallbackTypes())) {
      throw new AvatarException(sprintf('%s is an invalid fallback type', $type));
    }
    $this->fallbackType = $type;
    $this->fallbackURI = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setFallbackUri($uri) {
    $this->fallbackURI = $uri;
    $this->fallbackType = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  static public function getRatings() {
    return [
      'g' => 'G',
      'pg' => 'PG',
      'r' => 'R',
      'x' => 'X,'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRating() {
    return $this->rating;
  }

  /**
   * {@inheritdoc}
   */
  public function setRating($rating = NULL) {
    if (isset($rating) && !array_key_exists($rating, $this->getRatings())) {
      throw new AvatarException(sprintf('%s is an invalid rating', $rating));
    }
    $this->rating = $rating;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setIdentifier($identifier, $pre_hashed = FALSE) {
    if ($pre_hashed && strlen($identifier) > 32) {
      throw new AvatarException('API does not generate unique avatars after 32nd character.');
    }

    // Gravatar expects lower case email address.
    if (!$pre_hashed) {
      $identifier = strtolower($identifier);
    }

    return parent::setIdentifier($identifier, $pre_hashed);
  }

  /**
   * {@inheritdoc}
   */
  public static function hashIdentifier($identifier) {
    // Gravatar expects a md5 hash, and must have a length <= 32.
    // Override in case base class changes.
    return md5($identifier);
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    $kv = [];
    $url = ($this->isSecure() ? 'https://' : 'http://') . $this->getHostName() . '/avatar/';

    $identifier = $this->getIdentifier();
    if (!strlen($identifier)) {
      throw new AvatarException('Missing avatar identifier/hash');
    }

    $url .= $this->identifierIsPreHashed() ? $identifier : $this->hashIdentifier($identifier);

    $type = $this->getType();
    if (!in_array($type, $this->getFallbackTypes())) {
      if (isset($this->fallbackType)) {
        $kv['d'] = $this->fallbackType;
      }
      elseif (isset($this->fallbackURI)) {
        // Fallback URI is already urlencode'd by http_build_query().
        $kv['d'] = $this->fallbackURI;
      }
    }
    else {
      $type_map = $this->getTypesMap();
      if (!empty($type)) {
        $kv['d'] = $type_map[$type];
        $kv['f'] = 'y';
      }
    }

    if (is_numeric($this->width)) {
      $kv['s'] = $this->width;
    }

    if (isset($this->rating)) {
      $kv['r'] = $this->rating;
    }

    $query = http_build_query($kv);
    return !empty($query) ? $url . '?' . $query : $url;
  }

}
