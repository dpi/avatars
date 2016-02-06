<?php

/**
 * @file
 * Contains \Drupal\avatars\Permissions.
 */

namespace Drupal\avatars;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define a permission generator.
 */
class Permissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The avatar generator plugin manager.
   *
   * @var \Drupal\avatars\AvatarGeneratorPluginManagerInterface
   */
  protected $avatarGenerator;

  /**
   * Constructs the permission generator.
   *
   * @param \Drupal\avatars\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   */
  public function __construct(AvatarGeneratorPluginManagerInterface $avatar_generator) {
    $this->avatarGenerator = $avatar_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.avatar_generator')
    );
  }

  /**
   * Define permissions for picture providers.
   *
   * @return array
   *   An array of permissions.
   */
  public function avatarGenerators() {
    $permissions = [];

    /** @var \Drupal\avatars\AvatarGeneratorStorageInterface $avatars_generator_storage */
    $avatars_generator_storage = \Drupal::entityTypeManager()->getStorage('avatar_generator');
    foreach ($avatars_generator_storage->loadMultiple() as $instance) {
      if ($instance->getPlugin()->getPluginId() == 'user_preference') {
        continue;
      }

      $t_args = [
        '%label' => $instance->label(),
      ];
      $permissions["avatars avatar_generator user " . $instance->id()] = [
        'title' => $this->t('Use %label', $t_args),
        'description' => $this->t('User can select %label avatar generator.', $t_args),
      ];
    }

    return $permissions;
  }

}
