<?php

/**
 * @file
 * Contains \Drupal\avatars_gravatar\Plugin\AvatarGenerator\Retro.
 */

namespace Drupal\avatars_gravatar\Plugin\AvatarGenerator;

use Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\avatars_gravatar\Gravatar as GravatarAPI;

/**
 * Retro avatar generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar_retro",
 *   label = @Translation("Retro"),
 *   description = @Translation("8-bit style avatar from Gravatar.com"),
 *   fallback = TRUE,
 *   dynamic = FALSE,
 *   remote = TRUE
 * )
 */
class Retro extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($this->getIdentifier($account))
      ->setType('retro')
      ->setDimensions(256)
      ->getUrl();
  }

}
