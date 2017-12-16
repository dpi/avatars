<?php

namespace Drupal\avatars\Entity;

use Drupal\Component\Plugin\LazyPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\avatars\AvatarKitServicePluginManagerInterface;
use Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface as AvatarKitServicePluginInterface;

/**
 * Defines storage for an avatar service instance.
 *
 * @ConfigEntityType(
 *   id = "avatars_service",
 *   label = @Translation("Avatar Kit service instance"),
 *   label_singular = @Translation("Avatar Kit service instance"),
 *   label_plural = @Translation("Avatar Kit service instances"),
 *   label_count = @PluralTranslation(
 *     singular = "@count service instance",
 *     plural = "@count service instances",
 *   ),
 *   config_prefix = "service",
 *   admin_permission = "administer avatars",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\avatars\Form\Entity\AvatarKitServiceForm",
 *       "delete" = "Drupal\avatars\Form\Entity\AvatarKitServiceDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "storage" = "Drupal\avatars\Entity\AvatarKitServiceStorage",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/people/avatars/services/add",
 *     "canonical" = "/admin/config/people/avatars/services/{avatars_service}",
 *     "delete-form" = "/admin/config/people/avatars/services/{avatars_service}/delete",
 *     "edit-form" = "/admin/config/people/avatars/services/{avatars_service}/edit"
 *   },
 * )
 */
class AvatarKitService extends ConfigEntityBase implements AvatarKitServiceInterface {

  /**
   * Unique identifier.
   *
   * @var string
   */
  protected $id;

  /**
   * User friendly label.
   *
   * @var string
   */
  protected $label;

  /**
   * Fallback weight.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * ID of plugin.
   *
   * @var string
   *
   * @see \Drupal\avatars\Annotation\AvatarKitService::$id
   */
  protected $plugin;

  /**
   * Settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The plugin collection that holds the plugin for this entity.
   *
   * Static cache only.
   *
   * @var \Drupal\sms\Plugin\SmsGatewayPluginCollection
   */
  protected $pluginCollection;

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight(int $weight) : AvatarKitServiceInterface {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() : string {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() : ?AvatarKitServicePluginInterface {
    if ($this->getPluginCollection()) {
      return $this->getPluginCollection()->get($this->getPluginId());
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() : array {
    return ['settings' => $this->getPluginCollection()];
  }

  /**
   * Get the Avatar Kit service plugin manager.
   *
   * @return \Drupal\avatars\AvatarKitServicePluginManagerInterface
   *   The Avatar Kit service plugin manager.
   */
  protected function getAvatarServiceServicePluginManager() : AvatarKitServicePluginManagerInterface {
    return \Drupal::service('plugin.manager.avatars_services');
  }

  /**
   * Encapsulates a lazy plugin collection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection|null
   *   The plugin collection.
   */
  protected function getPluginCollection() : ?LazyPluginCollection {
    if (!$this->pluginCollection && $this->plugin) {
      $args = [
        $this->getAvatarServiceServicePluginManager(),
        $this->plugin,
        $this->settings,
      ];
      $this->pluginCollection = new DefaultSingleLazyPluginCollection(...$args);
    }
    return $this->pluginCollection;
  }

}
