<?php

/**
 * @file
 * Contains \Drupal\ak\AvatarGeneratorPluginManager.
 */

namespace Drupal\ak;

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
    parent::__construct('Plugin/AvatarGenerator', $namespaces, $module_handler, 'Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorPluginInterface', 'Drupal\ak\Annotation\AvatarGenerator');
    $this->alterInfo('avatar_generator_info');
    $this->setCacheBackend($cache_backend, 'avatar_generator_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = array()) {
    return 'broken';
  }

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