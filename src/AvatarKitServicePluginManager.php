<?php

namespace Drupal\avatars;

use Drupal\avatars\Annotation\AvatarKitService;
use Drupal\avatars\Event\AvatarKitEvents;
use Drupal\avatars\Event\AvatarKitServiceDefinitionAlterEvent;
use Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Avatar Kit service plugin manager.
 */
class AvatarKitServicePluginManager extends DefaultPluginManager implements AvatarKitServicePluginManagerInterface {

  /**
   * An event dispatcher instance to use for events.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   An event dispatcher instance to use for events.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, EventDispatcherInterface $eventDispatcher) {
    parent::__construct('Plugin/Avatars/Service', $namespaces, $module_handler, AvatarKitServiceInterface::class, AvatarKitService::class);
    $this->setCacheBackend($cache_backend, 'avatar_service_plugins');
    $this->eventDispatcher = $eventDispatcher;
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

  /**
   * {@inheritdoc}
   */
  protected function alterDefinitions(&$definitions) {
    // Don't call parent since we don't want to use alter hooks.
    $event = (new AvatarKitServiceDefinitionAlterEvent())
      ->setDefinitions($definitions);
    /** @var \Drupal\avatars\Event\AvatarKitServiceDefinitionAlterEvent $event */
    $event = $this->eventDispatcher
      ->dispatch(AvatarKitEvents::PLUGIN_SERVICE_ALTER, $event);
    $definitions = $event->getDefinitions();
  }

}
