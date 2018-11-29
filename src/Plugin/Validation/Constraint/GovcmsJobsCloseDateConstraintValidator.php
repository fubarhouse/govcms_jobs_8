<?php

namespace Drupal\govcms_jobs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the GovcmsJobsCloseDateConstraint.
 */
class GovcmsJobsCloseDateConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $entity = $field->getEntity();
    $pub_value = $entity->field_publication_date->getValue();
    $close_value = $field->getValue();
    if (!empty($pub_value) && !empty($close_value)) {
      $pub_time = strtotime($pub_value[0]['value']);
      $close_time = strtotime($close_value[0]['value']);
      if ($close_time < $pub_time) {
        $this->context->addViolation($constraint->message);
      }
    }
  }

}
