<?php

/**
 * @file
 * Contains \Drupal\ak_gravatar\Plugin\AvatarGenerator\Monsterid.
 */

namespace Drupal\ak_gravatar\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_gravatar\Gravatar as GravatarAPI;

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
      ->setIdentifier($account->getEmail())
      ->setType('monsterid')
      ->setDimensions(256)
      ->getUrl();
  }

}
