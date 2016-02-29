<?php

/**
 * @file
 * Contains \Drupal\avatars_test\Plugin\AvatarGenerator\DynamicTestGenerator.
 */

namespace Drupal\avatars_test\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Dynamic test avatar generator.
 *
 * @AvatarGenerator(
 *   id = "avatars_test_dynamic",
 *   label = @Translation("Dynamic Test Generator"),
 *   description = @Translation("Dynamic test avatar generator."),
 *   fallback = TRUE,
 *   dynamic = TRUE,
 *   remote = TRUE
 * )
 */
class DynamicTestGenerator extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    return Url::fromRoute('avatars_test.image')->setAbsolute()->toString();
  }

}
