<?php

/**
 * @file
 * Contains \Drupal\ak_gravatar\Plugin\AvatarGenerator\Gravatar.
 */

namespace Drupal\ak_gravatar\Plugin\AvatarGenerator;

use Drupal\ak\Plugin\AvatarGenerator\AvatarGeneratorBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\ak_gravatar\Gravatar as GravatarAPI;

/**
 * Gravatar avatar generator.
 *
 * @AvatarGenerator(
 *   id = "gravatar",
 *   label = @Translation("Gravatar"),
 *   description = @Translation("Universal avatar uploaded to Gravatar.com"),
 *   fallback = FALSE,
 *   dynamic = TRUE,
 *   remote = TRUE
 * )
 */
class Gravatar extends AvatarGeneratorBase {

  function generateURI(AccountInterface $account){
    $gravatar = new GravatarAPI();
    return $gravatar
      ->setIdentifier($account->getEmail())
      ->setType('gravatar')
      ->setFallbackType('404')
      ->setDimensions(256)
      ->getUrl();
  }

}
