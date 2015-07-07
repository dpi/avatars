<?php

/**
 * @file
 * Contains \Drupal\ak_gravatar\Plugin\AvatarGenerator\Retro.
 */

namespace Drupal\ak_gravatar\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_gravatar\Gravatar as GravatarAPI;

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

  function generateURI(AccountInterface $account){
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($account->getEmail())
      ->setType('retro')
      ->setDimensions(256)
      ->getUrl();
  }

}