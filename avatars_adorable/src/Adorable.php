<?php

/**
 * @file
 * Contains \Drupal\avatars_adorable\Adorable.
 */

namespace Drupal\avatars_adorable;

use Drupal\avatars\AvatarBase;
use Drupal\avatars\Exception\AvatarException;

/**
 * Implements the Adorable.io API.
 */
class Adorable extends AvatarBase implements AdorableInterface {

  /**
   * Constructs a new Adorable object.
   */
  public function __construct() {
    $this->setDimensionConstraints(
      Adorable::DIMENSION_MINIMUM_WIDTH,
      Adorable::DIMENSION_MAXIMUM_WIDTH
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getHostName() {
    $hostname = parent::getHostName();
    return isset($hostname) ? $hostname : $this::ADORABLE_HOSTNAME;
  }

  /**
   * {@inheritdoc}
   */
  static public function getTypes() {
    return [
      'adorable' => 'Adorables',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setIsSecure($secure_request = TRUE) {
    if ($secure_request) {
      throw new AvatarException('Adorable does not support secure requests.');
    }
    return parent::setIsSecure($secure_request);
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    $url = ($this->isSecure() ? 'https://' : 'http://') . $this->getHostName() . '/avatars/';

    // Width can be omitted.
    if (is_numeric($this->width)) {
      $url .= $this->width . '/';
    }

    $identifier = $this->getIdentifier();
    if (empty($identifier)) {
      throw new AvatarException('Missing avatar identifier/hash');
    }

    $url .= !$this->identifierIsPreHashed() ? $this->hashIdentifier($identifier) : $url;

    return $url;
  }

}
