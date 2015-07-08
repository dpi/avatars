<?php

/**
 * @file
 * Contains \Drupal\ak\Plugin\AvatarGenerator\User.
 */

namespace Drupal\ak\Plugin\AvatarGenerator;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User as CoreUser;

/**
 * User plugin.
 *
 * @AvatarGenerator(
 *   id = "user",
 *   label = @Translation("User upload"),
 *   dynamic = TRUE,
 *   fallback = FALSE,
 *   remote = FALSE
 * )
 */
class User extends AvatarGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function getFile(AccountInterface $account) {
    if (!$account->isAnonymous() && $user = CoreUser::load($account->id())) {
      $entities = $user->{AK_FIELD_PICTURE_USER}->referencedEntities();
      return reset($entities);
    }
    return NULL;
  }

}
