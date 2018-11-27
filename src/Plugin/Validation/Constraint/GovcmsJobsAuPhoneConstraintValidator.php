<?php

namespace Drupal\govcms_jobs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the GovcmsJobsAuPhoneConstraint.
 */
class GovcmsJobsAuPhoneConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $number = preg_replace('/\s+/',"", $field->getValue()[0]['value']);
    if (!preg_match('/^\({0,1}((0|\+61)(2|4|3|7|8)){0,1}\){0,1}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{1}(\ |-){0,1}[0-9]{3}$/', $number)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
