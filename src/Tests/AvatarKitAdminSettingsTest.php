<?php

/**
 * @file
 * Contains \Drupal\avatars\Tests\AvatarKitAdminSettingsTest.
 */

namespace Drupal\avatars\Tests;

use Drupal\Component\Utility\Unicode;

/**
 * Avatar Kit admin settings test.
 *
 * @group avatars
 */
class AvatarKitAdminSettingsTest extends AvatarKitWebTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $user = $this->createUser(['administer avatars']);
    $this->drupalLogin($user);
  }

  /**
   * Test admin settings.
   */
  function testAdminSettings() {
    $avatar_generator1 = $this->createAvatarGenerator(['weight' => -100, 'status' => 1]);
    $avatar_generator2 = $this->createAvatarGenerator(['weight' => 100]);

    $this->drupalGet('admin/config/people/avatars');
    $this->assertResponse(200);
    $this->assertRaw(t('A list of avatar generators to try for each user in order of preference.'));

    // Generator 1 should be in first row, with checked box.
    $elements = $this->xpath('//table//tr[1]/td[1][text()=:label]', [
      ':label' => $avatar_generator1->label()
    ]);
    $this->assertTrue(!empty($elements), 'Generator on first row.');
    $this->assertFieldChecked('edit-avatar-generators-' . $avatar_generator1->id() . '-enabled');

    // Generator 2 should be in fourth row, with unchecked box.
    $elements = $this->xpath('//table//tr[4]/td[1][text()=:label]', [
      ':label' => $avatar_generator2->label()
    ]);
    $this->assertTrue(!empty($elements), 'Generator on fourth row.');
    $this->assertNoFieldChecked('edit-avatar-generators-' . $avatar_generator2->id() . '-enabled');
  }

  /**
   * Test add avatar generator config.
   */
  function testGeneratorAdd() {
    $this->drupalGet('admin/config/people/avatars/generators/add');
    $this->assertResponse(200);

    $id = Unicode::strtolower($this->randomMachineName());
    $label = $this->randomString();
    $edit = [
      'label' => $label,
      'id' => $id,
      'plugin' => 'avatars_test_dynamic',
    ];
    $this->drupalPostForm('admin/config/people/avatars/generators/add', $edit, t('Save'));

    $t_args = ['%label' => $label];
    $this->assertRaw(t('Created avatar generator %label', $t_args));
    $this->assertUrl('admin/config/people/avatars/generators/' . $id);
  }

  /**
   * Test edit avatar generator config.
   */
  function testGeneratorEdit() {
    $avatar_generator = $this->createAvatarGenerator();

    $this->drupalGet($avatar_generator->toUrl('edit-form'));
    $this->assertResponse(200);

    $t_args = ['%label' => $avatar_generator->label()];
    $this->assertRaw(t('Edit avatar generator %label', $t_args));

    $edit = ['label' => $avatar_generator->label()];
    $this->drupalPostForm($avatar_generator->toUrl('edit-form'), $edit, t('Save'));
    $this->assertUrl('admin/config/people/avatars');
    $this->assertRaw(t('Updated avatar generator %label', $t_args));
  }

  /**
   * Test delete avatar generator config.
   */
  function testGeneratorDelete() {
    $avatar_generator = $this->createAvatarGenerator();
    $this->drupalGet($avatar_generator->toUrl('delete-form'));
    $this->assertResponse(200);

    $t_args = ['%label' => $avatar_generator->label()];
    $this->assertRaw(t('Are you sure you want to delete avatar generator %label?', $t_args));

    $this->drupalPostForm($avatar_generator->toUrl('delete-form'), [], t('Delete'));
    $this->assertUrl('admin/config/people/avatars');
    $this->assertRaw(t('Avatar generator %label was deleted.', $t_args));
  }


  function testGenerator() {
    $this->deleteAvatarGenerators();

    $avatar_generator1 = $this->createAvatarGenerator();
    $this->setAvatarGeneratorPreferences([$avatar_generator1->id() => TRUE]);

    $user = $this->createUser(['administer avatars','avatars avatar_generator user ' . $avatar_generator1->id()]);
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/people/avatars');

    /** @var \Drupal\avatars\AvatarManagerInterface $am */
    $am = \Drupal::service('avatars.avatar_manager');
    debug($am->findValidAvatar($user));

    $this->drupalGet($user->toUrl());
  }

}
