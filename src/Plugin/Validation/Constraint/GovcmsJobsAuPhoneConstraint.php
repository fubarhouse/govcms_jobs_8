<?php

namespace Drupal\govcms_jobs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks Australia Phone number is valid.
 *
 * @Constraint(
 *   id = "GovcmsJobsAuPhoneConstraint",
 *   label = @Translation("GovcmsJobs Au Phone Constraint", context = "Validation"),
 *   type = FALSE
 * )
 */
class GovcmsJobsAuPhoneConstraint extends Constraint {

  public $message = 'Phone number is invalid.';

}
