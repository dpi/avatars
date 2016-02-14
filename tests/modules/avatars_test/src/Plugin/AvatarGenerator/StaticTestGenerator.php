<?php

/**
 * @file
 * Contains \Drupal\avatars_test\Plugin\AvatarGenerator\StaticTestGenerator.
 */

namespace Drupal\avatars_test\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Static test avatar generator.
 *
 * @AvatarGenerator(
 *   id = "avatars_test_static",
 *   label = @Translation("Static Test Generator"),
 *   description = @Translation("Static test avatar generator."),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class StaticTestGenerator extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    return 'http://example.com/image.jpg';
  }

}
