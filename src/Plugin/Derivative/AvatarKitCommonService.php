<?php

namespace Drupal\avatars\Plugin\Derivative;

use dpi\ak\AvatarServiceDiscoveryInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates services for each Avatar Kit library service.
 */
class AvatarKitCommonService extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The base plugin ID this derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * Avatar Kit service discovery.
   *
   * @var \dpi\ak\AvatarServiceDiscoveryInterface
   */
  protected $avatarKitServiceDiscovery;

  /**
   * Constructs an EntityDeriver object.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \dpi\ak\AvatarServiceDiscoveryInterface $avatarKitServiceDiscovery
   *   Avatar Kit service discovery.
   */
  public function __construct($base_plugin_id, AvatarServiceDiscoveryInterface $avatarKitServiceDiscovery) {
    $this->basePluginId = $base_plugin_id;
    $this->avatarKitServiceDiscovery = $avatarKitServiceDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('avatars.avatar_kit.discovery.services')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) : array {
    $services = $this->avatarKitServiceDiscovery->getAvatarServices();
    foreach ($services as $service) {
      $id = $service->id;
      $this->derivatives[$id] = $base_plugin_definition;
      $this->derivatives[$id]['label'] = $service->name;
      $this->derivatives[$id]['description'] = $service->description;
      $this->derivatives[$id]['dynamic'] = !empty($service->is_dynamic);
    }
    return $this->derivatives;
  }

}
