<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarManager.
 */

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarPreview;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

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
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

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
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   The cache tag invalidator.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\avatars\AvatarGeneratorPluginManagerInterface $avatar_generator
   *   The avatar generator plugin manager.
   */
  function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client, CacheTagsInvalidatorInterface $cache_tag_invalidator, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, AvatarGeneratorPluginManagerInterface $avatar_generator) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->cacheTagInvalidator = $cache_tag_invalidator;
    $this->loggerFactory = $logger_factory;
    $this->avatarGeneratorStorage = $entity_type_manager
      ->getStorage('avatar_generator');
    $this->avatarGenerator = $avatar_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function syncAvatar(UserInterface $user) {
    if ($user->isAnonymous()) {
      return;
    }

    $field_item_list = &$user->{AK_FIELD_PICTURE_ACTIVE};
    $file1 = isset($field_item_list->entity) ? $field_item_list->entity : NULL;

    $avatar_preview = $this->findValidAvatar($user);
    $file2 = $avatar_preview ? $avatar_preview->getAvatar() : NULL;

    if ($file1 !== $file2) {
      $user->{AK_FIELD_PICTURE_ACTIVE} = $file2;
      $user->save();
    }
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function refreshAvatarGenerator(UserInterface $user, AvatarGeneratorInterface $avatar_generator, $scope) {
    if ($user->isNew()) {
      return FALSE;
    }

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
   * {@inheritdoc}
   */
  function refreshAllAvatars(UserInterface $user) {
    $previews = [];
    foreach ($this->getAvatarGeneratorsForUser($user) as $avatar_generator) {
      $avatar_preview = $this->refreshAvatarGenerator($user, $avatar_generator, AvatarPreviewInterface::SCOPE_TEMPORARY);
      if ($avatar_preview) {
        $previews[] = $avatar_preview;
      }
    }
    return $previews;
  }

  /**
   * {@inheritdoc}
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
          if (($result = $this->httpClient->get($url)) && ($result->getStatusCode() == 200)) {
            $file_path = $directory . '/' . $user->id() . '.jpg';
            $file = file_save_data($result->getBody(), $file_path, FILE_EXISTS_REPLACE);
          }
        }
        catch (ClientException $e) {
          // 4xx errors are acceptable, do not need to log.
          return FALSE;
        }
        catch (\Exception $e) {
          $this->loggerFactory
            ->get('avatars')
            ->error($this->t('Failed to get @id avatar for @generator: %exception', [
              '@id' => $user->id(),
              '@generator' => $avatar_generator->id(),
              '%exception' => $e->getMessage(),
            ]));
          return FALSE;
        }
      }
    }

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferences(UserInterface $user) {
    $avatar_generators = $this->getAvatarGeneratorsForUser($user, FALSE);
    uasort($avatar_generators, '\Drupal\avatars\Entity\AvatarGenerator::sort');

    foreach ($avatar_generators as $avatar_generator) {
      $id = $avatar_generator->id();
      if ($avatar_generator->getPlugin()->getPluginId() == 'user_preference') {
        $id = $user->{AK_FIELD_AVATAR_GENERATOR}->value;
        $scope = AvatarPreviewInterface::SCOPE_USER_SELECTED;
      }
      else {
        $scope = AvatarPreviewInterface::SCOPE_SITE_FALLBACK;
      }

      // Catches empty user preference.
      if ($id) {
        yield $id => $scope;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  function invalidateUserAvatar(UserInterface $user) {
    if (isset($user->{AK_FIELD_PICTURE_ACTIVE}->entity)) {
      $this->cacheTagInvalidator->invalidateTags(
        $user->{AK_FIELD_PICTURE_ACTIVE}->entity->getCacheTags()
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  function notifyDynamicChange(AvatarGeneratorInterface $avatar_generator, UserInterface $user) {
    if ($avatar_preview = AvatarPreview::getAvatarPreview($avatar_generator, $user)) {
      $avatar_preview->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  function getAvatarGeneratorsForUser(UserInterface $user, $exclude_user_preference = TRUE) {
    $avatar_generators = [];
    foreach ($this->avatarGeneratorStorage->getEnabledAvatarGenerators() as $avatar_generator) {
      if ($avatar_generator->getPlugin()->getPluginId() == 'user_preference') {
        if ($exclude_user_preference) {
          continue;
        }
      }
      else if (!$user->hasPermission("avatars avatar_generator user " . $avatar_generator->id())) {
        continue;
      }
      $avatar_generators[] = $avatar_generator;
    }
    return $avatar_generators;
  }

}
