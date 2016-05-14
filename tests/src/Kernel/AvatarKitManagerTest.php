<?php

namespace Drupal\Tests\avatars\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\UserCreationTrait;
use Drupal\avatars\Entity\AvatarGenerator;

/**
 * Tests Avatar Manager.
 *
 * @group avatars
 * @coversDefaultClass \Drupal\avatars\AvatarManager
 */
class AvatarKitManagerTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['avatars', 'user', 'avatars_test', 'system'];

  /**
   * The avatar manager.
   *
   * @var \Drupal\avatars\AvatarManagerInterface
   */
  protected $avatarManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');

    $this->avatarManager = $this->container->get('avatars.avatar_manager');

    // Create uid=1, so sequential created users do not get all access.
    $this->createUser();
  }

  /**
   * Test default behaviour of testGetAvatarGeneratorsForUser().
   *
   * Get all avatar generators for a user excluding user_preference plugins.
   *
   * @covers ::getAvatarGeneratorsForUser
   */
  public function testGetAvatarGeneratorsForUser() {
    $generator_1 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'user_preference',
    ]);
    $generator_1
      ->setStatus(TRUE)
      ->save();

    $generator_2 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'avatars_test_static',
    ]);
    $generator_2
      ->setStatus(TRUE)
      ->save();

    $user = $this->createUser([
      'avatars avatar_generator user ' . $generator_2->id(),
    ]);

    $generators = $this->avatarManager->getAvatarGeneratorsForUser($user);
    $this->assertEquals(1, count($generators));
  }

  /**
   * Get all avatar generators for a user including user_preference plugins.
   *
   * @covers ::getAvatarGeneratorsForUser
   */
  public function testGetAvatarGeneratorsForUserWithPermissions() {
    $generator_1 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'user_preference',
    ]);
    $generator_1
      ->setStatus(TRUE)
      ->save();

    $generator_2 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'avatars_test_static',
    ]);
    $generator_2
      ->setStatus(TRUE)
      ->save();

    $user = $this->createUser([
      'avatars avatar_generator user ' . $generator_2->id(),
    ]);

    $generators = $this->avatarManager->getAvatarGeneratorsForUser($user, FALSE);
    $this->assertEquals(2, count($generators));
  }

  /**
   * Get all avatar generators for a user including user_preference plugins.
   *
   * @covers ::getAvatarGeneratorsForUser
   */
  public function testGetAvatarGeneratorsForUserWithoutPermissions() {
    $generator_1 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'avatars_test_static',
    ]);
    $generator_1
      ->setStatus(TRUE)
      ->save();

    $user = $this->createUser();

    $generators = $this->avatarManager->getAvatarGeneratorsForUser($user);
    $this->assertEquals(0, count($generators), 'User does not have access to any avatar generators.');
  }

  /**
   * Test access to avatar generators unavailable if status is disabled.
   *
   * @covers ::getAvatarGeneratorsForUser
   */
  public function testGetAvatarGeneratorsDisabledGenerators() {
    $generator_1 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'avatars_test_static',
    ]);
    $generator_1
      ->setStatus(FALSE)
      ->save();

    $user = $this->createUser([
      'avatars avatar_generator user ' . $generator_1->id(),
    ]);

    $generators = $this->avatarManager->getAvatarGeneratorsForUser($user);
    $this->assertEquals(0, count($generators));
  }

}
