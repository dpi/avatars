<?php

/**
 * @file
 * Contains \Drupal\avatars\Entity\AvatarGenerator.
 */

namespace Drupal\avatars\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\avatars\AvatarGeneratorInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\avatars\AvatarGeneratorPluginCollection;

/**
 *
 * Defines storage for an avatar generator configuration.
 *
 * @ConfigEntityType(
 *   id = "avatar_generator",
 *   label = @Translation("Avatar Generator"),
 *   config_prefix = "generator",
 *   admin_permission = "administer avatars",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   handlers = {
 *     "storage" = "Drupal\avatars\AvatarGeneratorStorage",
 *     "form" = {
 *       "default" = "Drupal\avatars\Form\AvatarGeneratorForm",
 *       "add" = "Drupal\avatars\Form\AvatarGeneratorForm",
 *       "edit" = "Drupal\avatars\Form\AvatarGeneratorForm",
 *       "delete" = "Drupal\avatars\Form\AvatarGeneratorDeleteForm",
 *     }
 *   },
 *   links = {
 *     "canonical" = "/admin/config/people/avatars/generators/{avatar_generator}",
 *     "edit-form" = "/admin/config/people/avatars/generators/{avatar_generator}",
 *     "delete-form" = "/admin/config/people/avatars/generators/{avatar_generator}/delete",
 *   },
 * )
 */
class AvatarGenerator extends ConfigEntityBase implements AvatarGeneratorInterface, EntityWithPluginCollectionInterface {

  /**
   * Unique identifier.
   *
   * @var int
   */
  protected $id;

  /**
   * Label
   *
   * @var string
   */
  protected $label;

  /**
   * Weight
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * Status
   *
   * @var bool
   */
  protected $status;

  /**
   * An avatar generator plugin ID.
   *
   * @var bool
   */
  protected $plugin;

  /**
   * Module defining the associated avatar generator plugin.
   *
   * @var string
   */
  protected $provider;

  /**
   * Settings for the avatar generator plugin.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The plugin collection that holds the plugin for this entity.
   *
   * @var \Drupal\avatars\AvatarGeneratorPluginCollection
   */
  protected $pluginCollection;

  /**
   * Encapsulates the creation of this avatar generator's LazyPluginCollection.
   *
   * @return \Drupal\avatars\AvatarGeneratorPluginCollection
   *   The avatar generators's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $this->pluginCollection = new AvatarGeneratorPluginCollection(\Drupal::service('plugin.manager.avatar_generator'), $this->plugin, $this->settings);
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['settings' => $this->getPluginCollection()];
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

}
