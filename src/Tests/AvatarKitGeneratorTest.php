<?php

namespace Drupal\avatars\Tests;

use Drupal\avatars\Entity\AvatarGenerator;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;

/**
 * Avatar Kit generator test.
 *
 * @group avatars
 */
class AvatarKitGeneratorTest extends AvatarKitWebTestBase {

  /**
   * Test avatar generators.
   */
  function testGenerator() {
    $this->deleteAvatarGenerators();

    $avatar_generator1 = $this->createAvatarGenerator();

    $user = $this->createUser([
      'administer avatars',
      'avatars avatar_generator user ' . $avatar_generator1->id(),
    ]);
    $this->drupalLogin($user);

    $this->setAvatarGeneratorPreferences([$avatar_generator1->id() => TRUE]);
    $this->drupalGet('admin/config/people/avatars');

    /** @var \Drupal\avatars\AvatarManagerInterface $am */
    $am = \Drupal::service('avatars.avatar_manager');
    $avatar_preview = $am->findValidAvatar($user);
    $this->assertTrue($avatar_preview instanceof AvatarPreviewInterface, 'Downloaded avatar');
  }

  /**
   * Ensure requesting avatar for anonymous does not crash the site.
   */
  public function testAnonymous() {
    $generator_1 = AvatarGenerator::create([
      'label' => $this->randomMachineName(),
      'id' => $this->randomMachineName(),
      'plugin' => 'avatars_test_static',
    ]);
    $generator_1
      ->setStatus(TRUE)
      ->save();

    // The anonymous role must be granted access to at least on generator
    // otherwise nothing will tested.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('avatars avatar_generator user ' . $generator_1->id())
      ->save();

    $anonymous = User::getAnonymousUser();

    /** @var \Drupal\avatars\AvatarManagerInterface $am */
    $am = \Drupal::service('avatars.avatar_manager');
    $am->syncAvatar($anonymous);
  }
}
