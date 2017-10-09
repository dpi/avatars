<?php

namespace Drupal\avatars;

use dpi\ak\Annotation\AvatarService;
use dpi\ak\AvatarKit\AvatarServices\AvatarServiceInterface;
use dpi\ak\AvatarServiceDiscovery;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\UseCacheBackendTrait;

/**
 * Avatar Kit library service discovery.
 */
class AvatarKitAvatarServiceDiscovery extends AvatarServiceDiscovery {

  use UseCacheBackendTrait;

  /**
   * The class loader.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  protected $classLoader;

  /**
   * Backend for caching discovery results.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Constructs a new AvatarKitAvatarServiceDiscovery object.
   *
   * @param object $class_loader
   *   The class loader.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Backend for caching discovery results.
   */
  public function __construct($class_loader, CacheBackendInterface $cache_backend) {
    $this->subdir = 'AvatarKit/AvatarServices/';
    $this->serviceInterface = AvatarServiceInterface::class;
    $this->annotationClass = AvatarService::class;
    $this->cacheBackend = $cache_backend;

    $cache_key = 'avatars_ak_service_annotations';
    $annotations = $this->cacheGet($cache_key)->data ?? NULL;
    if (!isset($annotations)) {
      $root_namespaces = $class_loader->getPrefixesPsr4();
      $classes = $this->discoverClasses($root_namespaces);
      $annotations = $this->discoverAnnotations($classes);
      $this->cacheSet($cache_key, $annotations, Cache::PERMANENT);
    }

    $this->annotations = $annotations;
  }

}
