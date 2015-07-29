<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Plugin\AvatarGenerator\Monsterid.
 */

namespace Drupal\avatars_gravatar\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_gravatar\Gravatar as GravatarAPI;

/**
 * Monster ID avatar generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar_monsterid",
 *   label = @Translation("Monster ID"),
 *   description = @Translation("Monster ID from Gravatar.com"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Monsterid extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($this->getIdentifier($account))
      ->setType('monsterid')
      ->setDimensions(256)
      ->getUrl();
  }

}
