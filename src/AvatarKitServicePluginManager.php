<?php

namespace Drupal\avatars;

use Drupal\avatars\Annotation\AvatarKitService;
use Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Avatar Kit service plugin manager.
 */
class AvatarKitServicePluginManager extends DefaultPluginManager implements AvatarKitServicePluginManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Avatars/Service', $namespaces, $module_handler, AvatarKitServiceInterface::class, AvatarKitService::class);
    $this->alterInfo('avatar_service_plugins');
    $this->setCacheBackend($cache_backend, 'avatar_service_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() : array {
    $definitions = parent::findDefinitions();

    $definitions = array_filter($definitions, function (array $definition) : bool {
      // Don't remember abstract plugins.
      // This accommodates Avatar Kit deriver, where the base class is abstract.
      // It allows initial plugin creation and discovery. Later on, plugins add
      // features with their own class.
      $class = $definition['class'] ?? '';
      try {
        $reflection = new \ReflectionClass($class);
        return !$reflection->isAbstract();
      }
      catch (\ReflectionException $e) {
      }
      return FALSE;
    });

    return $definitions;
  }

}
