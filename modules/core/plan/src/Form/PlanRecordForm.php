<?php

namespace Drupal\plan\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for plan_record entities.
 *
 * @ingroup plan
 */
class PlanRecordForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Hide the plan reference field.
    $form['plan']['#access'] = FALSE;

    return $form;
  }

}
