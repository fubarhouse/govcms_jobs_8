<?php

namespace Drupal\govcms_jobs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the GovcmsJobsPubDateConstraint.
 */
class GovcmsJobsPubDateConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    if (strtotime($field->getValue()[0]['value']) < time()) {
      $this->context->addViolation($constraint->message);
    }
  }

}
