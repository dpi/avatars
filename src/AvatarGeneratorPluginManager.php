<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarGeneratorPluginManager.
 */

namespace Drupal\avatars;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of avatar generator plugins.
 */
class AvatarGeneratorPluginManager extends DefaultPluginManager implements AvatarGeneratorPluginManagerInterface, FallbackPluginManagerInterface {

  /**
   * Constructs a new avatar generator plugin manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/AvatarGenerator', $namespaces, $module_handler, 'Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorPluginInterface', 'Drupal\avatars\Annotation\AvatarGenerator');
    $this->alterInfo('avatar_generator_info');
    $this->setCacheBackend($cache_backend, 'avatar_generator_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'broken';
  }

  /**
   * Gets the definition of all non broken plugins for this type.
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    foreach ($definitions as $k => $definition) {
      if (isset($definition['id']) && $definition['id'] == 'broken') {
        unset($definitions[$k]);
      }
    }
    return $definitions;
  }

}
