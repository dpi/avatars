<?php

namespace Drupal\avatars;

use dpi\ak\AvatarIdentifierInterface;

/**
 * Wraps an avatar identifier object with Drupal entity support.
 *
 * Encapsulates any object implementing \dpi\ak\AvatarIdentifierInterface while
 * adding Drupal entity specific functionality.
 *
 * Proxies some methods back to an avatar identifier object.
 */
class EntityAvatarIdentifierProxy extends EntityAvatarIdentifier {

  /**
   * An avatar identifier object.
   *
   * @var \dpi\ak\AvatarIdentifierInterface
   */
  protected $original;

  /**
   * Creates a new EntityAvatarIdentifierProxy object.
   *
   * @param \dpi\ak\AvatarIdentifierInterface $identifier
   *   An avatar identifier object.
   */
  public function __construct(AvatarIdentifierInterface $identifier) {
    $this->original = $identifier;
  }

  /**
   * {@inheritdoc}
   */
  public function getRaw() {
    return $this->original->getRaw();
  }

  /**
   * {@inheritdoc}
   */
  public function setRaw(string $raw) {
    return $this->original->setRaw($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function getHashed() {
    return $this->original->getHashed();
  }

  /**
   * {@inheritdoc}
   */
  public function setHashed(string $string) {
    return $this->original->setHashed($string);
  }

  /**
   * {@inheritdoc}
   */
  public function setHasher(callable $callable) {
    return $this->original->setHasher($callable);
  }

}
