<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarManager.
 */

namespace Drupal\avatars;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Drupal\avatars\Entity\AvatarPreview;
use Drupal\user\UserInterface;
use Drupal\file\FileInterface;

/**
 * Provides an avatar manager service.
 */
class AvatarManager implements AvatarManagerInterface {

  use ContainerAwareTrait;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cache tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagInvalidator;

  /**
   * The avatar generator plugin manager.
   *
   * @var \Drupal\avatars\AvatarGeneratorPluginManagerInterface
   */
  protected $avatarGenerator;

  /**
   * Constructs a new AvatarManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   The cache tag invalidator.
   * @param \Drupal\avatars\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   */
  function __construct(ConfigFactoryInterface $config_factory, CacheTagsInvalidatorInterface $cache_tag_invalidator, AvatarGeneratorPluginManagerInterface $avatar_generator) {
    $this->configFactory = $config_factory;
    $this->cacheTagInvalidator = $cache_tag_invalidator;
    $this->avatarGenerator = $avatar_generator;
  }

  /**
   * Check user avatar for changes, and inserts the avatar into the user entity.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   */
  public function syncAvatar(UserInterface $user) {
    if ($avatar_preview = $this->findValidAvatar($user)) {
      $user->{AK_FIELD_PICTURE_ACTIVE} = $avatar_preview->getAvatar();
    }
    else {
      unset($user->{AK_FIELD_PICTURE_ACTIVE});
    }
    $user->save();
  }

  /**
   * Downloads all avatar previews for a user.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   */
  function refreshAllAvatars(UserInterface $user) {
    $definitions = $this->avatarGenerator->getDefinitions();
    foreach (array_keys($definitions) as $avatar_generator) {
      $this->refreshAvatarGenerator($user, $avatar_generator, AvatarPreviewInterface::SCOPE_TEMPORARY);
    }
  }

  /**
   * Go down the the avatar generator preference hierarchy for a user, loading
   * each avatar until a valid avatar is found.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface|NULL
   *   An avatar preview entity.
   */
  function findValidAvatar(UserInterface $user) {
    foreach ($this->getPreferences($user) as $avatar_generator => $scope) {
      if ($this->avatarGenerator->getDefinition($avatar_generator, FALSE)) {
        $this->refreshAvatarGenerator($user, $avatar_generator, $scope);
        if ($avatar_preview = AvatarPreview::getAvatarPreview($avatar_generator, $user)) {
          if ($avatar_preview->getAvatar()) {
            return $avatar_preview;
          }
        }
      }
    }
    return NULL;
  }

  /**
   * Create avatar if it does not exist.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   * @param string $avatar_generator
   *   An avatar generator plugin ID.
   * @param int $scope
   *   Scope level.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface
   *   An avatar preview entity.
   */
  public function refreshAvatarGenerator(UserInterface $user, $avatar_generator, $scope) {
    if ($avatar_preview = AvatarPreview::getAvatarPreview($avatar_generator, $user)) {
      if ($scope != AvatarPreviewInterface::SCOPE_TEMPORARY && $scope < $avatar_preview->getScope()) {
        $avatar_preview
          ->setScope($scope)
          ->save();
      }
    }
    else {
      $file = $this->getAvatarFile($avatar_generator, $user);
      $avatar_preview = AvatarPreview::create()
        ->setAvatarGenerator($avatar_generator)
        ->setAvatar($file instanceof FileInterface ? $file : NULL)
        ->setUser($user)
        ->setScope($scope);
      $avatar_preview->save();
    }

    return $avatar_preview;
  }

  /**
   * Download avatar and insert it into a file.
   *
   * Ignores any existing caches.
   *
   * @param string $avatar_generator
   *   The avatar generator plugin ID.
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\file\FileInterface|NULL
   */
  function getAvatarFile($avatar_generator, UserInterface $user) {
    /** @var \Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorPluginInterface $plugin */
    $plugin = $this->avatarGenerator->createInstance($avatar_generator);

    // Get avatar if it is already local.
    $file = $plugin->getFile($user);

    // Otherwise get the URL of the avatar, download it, and store it as a file.
    if (!$file && $url = $plugin->generateUri($user)) {
      $directory = 'public://avatar_kit/' . $avatar_generator;
      if (file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
        try {
          $client = new Client();
          if (($result = $client->get($url)) && ($result->getStatusCode() == 200)) {
            $file_path = $directory . '/' . $user->id() . '.jpg';
            $file = file_save_data($result->getBody(), $file_path, FILE_EXISTS_REPLACE);
          }
        }
        catch (ClientException $e) {
        }
      }
    }

    return $file;
  }

  /**
   * Avatar preference generators.
   *
   * Ordered by priority.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Generator
   *   Generator yield pairs:
   *   key: string $avatar_generator_machine_name
   *   value: value of constants prefixed with AvatarPreviewInterface::SCOPE_*
   */
  public function getPreferences(UserInterface $user) {
    $generators = $this->configFactory
      ->get('avatars.settings')
      ->get('avatar_generators');

    foreach ($generators as $generator) {
      if ($generator == '_user_preference') {
        $generator = $user->{AK_FIELD_AVATAR_GENERATOR}->value;
        $scope = AvatarPreviewInterface::SCOPE_USER_SELECTED;
      }
      else {
        $scope = AvatarPreviewInterface::SCOPE_SITE_FALLBACK;
      }

      if ($this->avatarGenerator->getDefinition($generator, FALSE)) {
        yield $generator => $scope;
      }
    }
  }

  /**
   * Invalidate any cache where the user avatar is displayed.
   *
   * Call if the avatar has changed, or is expected to change.
   *
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   */
  function invalidateUserAvatar(UserInterface $user) {
    if (isset($user->{AK_FIELD_PICTURE_ACTIVE}->entity)) {
      $this->cacheTagInvalidator->invalidateTags(
        $user->{AK_FIELD_PICTURE_ACTIVE}->entity->getCacheTags()
      );
    }
  }

  /**
   * Triggers expected change for dynamic avatar generator.
   *
   * @param string $avatar_generator
   *   An avatar generator plugin ID.
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   */
  function notifyDynamicChange($avatar_generator, UserInterface $user) {
    if ($avatar_preview = AvatarPreview::getAvatarPreview($avatar_generator, $user)) {
      $avatar_preview->delete();
    }
  }

}
