<?php

/**
 * @file
 * Contains \Drupal\avatars\Tests\AvatarKitWebTestBase.
 */

namespace Drupal\avatars\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Utility\Unicode;

/**
 * @todo
 */
abstract class AvatarKitWebTestBase extends WebTestBase {

  protected static $modules = ['avatars', 'avatars_test', 'user', 'file', 'field', 'image', 'options', 'unlimited_number'];

  /**
   * Avatar generator plugin storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $avatarGeneratorStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->avatarGeneratorStorage = $this->container->get('entity_type.manager')->getStorage('avatar_generator');
  }

  /**
   * Creates an avatar generator plugin instance.
   *
   * @param array $values
   *   Extra values for entity creation.
   *
   * @return \Drupal\avatars\AvatarGeneratorInterface
   *   An avatar generator plugin instance.
   */
  protected function createAvatarGenerator($values = []) {
    $avatar_generator = $this->avatarGeneratorStorage->create($values + [
      'id' => Unicode::strtolower($this->randomMachineName()),
      'label' => $this->randomString(),
      'plugin' => 'avatars_test_dynamic',
    ]);
    $avatar_generator->save();
    return $avatar_generator;
  }

  /**
   * Set avatar generator preferences.
   *
   * Ordered list of avatar generators, keyed by avatar generator plugin ID with
   * boolean value whether generator is to be enabled.
   */
  protected function setAvatarGeneratorPreferences(array $avatar_generators) {
    $weight = -10;
    foreach ($avatar_generators as $id => $status) {
      /** @var \Drupal\avatars\AvatarGeneratorInterface $avatar_generator */
      $avatar_generator = $this->avatarGeneratorStorage->load($id);
      $avatar_generator
        ->setWeight($weight)
        ->setStatus($status)
        ->save();
      $weight++;
    }
  }

  /**
   * Delete all existing avatar generator preferences.
   */
  protected function deleteAvatarGenerators() {
    $avatar_generators = $this->avatarGeneratorStorage->loadMultiple();
    $this->avatarGeneratorStorage->delete($avatar_generators);
  }

}
