<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarManager.
 */

namespace Drupal\avatars;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use Drupal\avatars\Entity\AvatarPreview;
use Drupal\user\UserInterface;
use Drupal\file\FileInterface;

/**
 * Provides an avatar manager service.
 */
class AvatarManager implements AvatarManagerInterface {

  use ContainerAwareTrait;
  use StringTranslationTrait;

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
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Storage for avatar generator storage entities.
   *
   * @var \Drupal\avatars\AvatarGeneratorStorageInterface
   */
  protected $avatarGeneratorStorage;

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
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\avatars\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   */
  function __construct(ConfigFactoryInterface $config_factory, CacheTagsInvalidatorInterface $cache_tag_invalidator, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, AvatarGeneratorPluginManagerInterface $avatar_generator) {
    $this->configFactory = $config_factory;
    $this->cacheTagInvalidator = $cache_tag_invalidator;
    $this->loggerFactory = $logger_factory;
    $this->avatarGeneratorStorage = $entity_type_manager
      ->getStorage('avatar_generator');
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
      $avatar_generator = $this->avatarGeneratorStorage->load($avatar_generator);
      if ($avatar_generator instanceof AvatarGeneratorInterface) {
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
   * @param \Drupal\avatars\AvatarGeneratorInterface $avatar_generator
   *   An avatar generator instance.
   * @param int $scope
   *   Caching scope level.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface|FALSE
   *   An avatar preview entity.
   */
  public function refreshAvatarGenerator(UserInterface $user, AvatarGeneratorInterface $avatar_generator, $scope) {
    if ($avatar_preview = AvatarPreview::getAvatarPreview($avatar_generator, $user)) {
      // @todo fix this block. does not make much sense.
      if ($scope != AvatarPreviewInterface::SCOPE_TEMPORARY && $scope != $avatar_preview->getScope()) {
        $avatar_preview
          ->setScope($scope)
          ->save();
      }
    }
    else {
      $file = $this->getAvatarFile($avatar_generator, $user);
      $avatar_preview = AvatarPreview::create()
        ->setAvatarGeneratorId($avatar_generator->id())
        ->setAvatar($file instanceof FileInterface ? $file : NULL)
        ->setUser($user)
        ->setScope($file instanceof FileInterface ? $scope : AvatarPreviewInterface::SCOPE_TEMPORARY);
      $avatar_preview->save();
    }

    return $avatar_preview;
  }

  /**
   * Downloads all avatar previews for a user.
   *
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\avatars\AvatarPreviewInterface[]
   *   An array of refreshed avatar preview entities.
   */
  function refreshAllAvatars(UserInterface $user) {
    $previews = [];
    $instances = $this->avatarGeneratorStorage->getEnabledAvatarGenerators();
    foreach ($instances as $avatar_generator) {
      if ($avatar_generator->getPlugin()->getPluginId() == 'user_preference') {
        continue;
      }
      $previews[] = $this->refreshAvatarGenerator($user, $avatar_generator, AvatarPreviewInterface::SCOPE_TEMPORARY);
    }
    return $previews;
  }

  /**
   * Download avatar and insert it into a file.
   *
   * Ignores any existing caches. Use refreshAvatarGenerator to take advantage
   * of internal caching.
   *
   * @param \Drupal\avatars\AvatarGeneratorInterface $avatar_generator
   *   An avatar generator instance.
   * @param \Drupal\user\UserInterface
   *   A user entity.
   *
   * @return \Drupal\file\FileInterface|FALSE
   */
  function getAvatarFile(AvatarGeneratorInterface $avatar_generator, UserInterface $user) {
    $plugin = $avatar_generator->getPlugin();

    // Get avatar if it is already local.
    $file = $plugin->getFile($user);

    // Otherwise get the URL of the avatar, download it, and store it as a file.
    if (!$file && $url = $plugin->generateUri($user)) {
      $directory = 'public://avatar_kit/' . $avatar_generator->id();
      if (file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
        try {
          $client = new Client();
          if (($result = $client->get($url)) && ($result->getStatusCode() == 200)) {
            $file_path = $directory . '/' . $user->id() . '.jpg';
            $file = file_save_data($result->getBody(), $file_path, FILE_EXISTS_REPLACE);
          }
        } catch (\Exception $e) {
          $this->loggerFactory
            ->get('avatars')
            ->error($this->t('Failed to get @id avatar for @generator: %exception', [
              '@id' => $user->id(),
              '@generator' => $avatar_generator->id(),
              '%exception' => $e->getMessage(),
            ]));
          return NULL;
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
    $instances = $this->avatarGeneratorStorage->getEnabledAvatarGenerators();
    uasort($instances, '\Drupal\avatars\Entity\AvatarGenerator::sort');

    foreach ($instances as $instance) {
      $id = $instance->id();
      if ($instance->getPlugin()->getPluginId() == 'user_preference') {
        $id = $user->{AK_FIELD_AVATAR_GENERATOR}->value;
        $scope = AvatarPreviewInterface::SCOPE_USER_SELECTED;
      }
      else {
        $scope = AvatarPreviewInterface::SCOPE_SITE_FALLBACK;
      }

      yield $id => $scope;
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
   * @param \Drupal\avatars\AvatarGeneratorInterface $avatar_generator
   *   An avatar generator instance.
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   */
  function notifyDynamicChange(AvatarGeneratorInterface $avatar_generator, UserInterface $user) {
    if ($avatar_preview = AvatarPreview::getAvatarPreview($avatar_generator, $user)) {
      $avatar_preview->delete();
    }
  }

}
