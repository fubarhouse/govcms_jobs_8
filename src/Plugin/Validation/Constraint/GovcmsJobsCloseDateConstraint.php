<?php

namespace Drupal\govcms_jobs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks Closing Date is valid.
 *
 * @Constraint(
 *   id = "GovcmsJobsCloseDateConstraint",
 *   label = @Translation("Govcms Jobs Closing Date Constraint", context = "Validation"),
 *   type = FALSE
 * )
 */
class GovcmsJobsCloseDateConstraint extends Constraint {

  public $message = 'Closing date must be the same or past the vacancy publication date.';

}
