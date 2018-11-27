<?php

namespace Drupal\govcms_jobs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks Publication Date is valid.
 *
 * @Constraint(
 *   id = "GovcmsJobsPubDateConstraint",
 *   label = @Translation("Govcms Jobs Publication Date Constraint", context = "Validation"),
 *   type = FALSE
 * )
 */
class GovcmsJobsPubDateConstraint extends Constraint {

  public $message = 'Publication date can\'t be in the past.';

}
