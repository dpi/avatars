<?php

/**
 * @file
 * Contains \Drupal\avatars\Tests\AvatarKitGeneratorTest.
 */

namespace Drupal\avatars\Tests;
use Drupal\avatars\AvatarPreviewInterface;

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

}
