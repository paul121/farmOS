<?php

namespace Drupal\farm_ui_views\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Entity\View;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;

class TableColumnPreferenceForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_table_column_preference';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ViewEntitYInterface $view = NULl) {

    // Bail if no view.
    if (empty($view)) {
      return $form;
    }

    // Get the view executable and init the default display.
    // @TODO: Support using this form for other displays?
    $executable = $view->getExecutable();
    $executable->initDisplay();
    $style = $executable->getStyle();

    // Bail if not a table style.
    if ($style->getPluginId() !== 'table') {
      return $form;
    }

    // Get field handlers.
    $type = 'field';
    $types = ViewExecutable::getHandlerTypes();
    $display = $executable->getDisplay();
    $field_handlers = $display->getOption($types[$type]['plural']);

    // Limit field handlers to ones included in the table.
    $columns =$style->options['columns'] ?? [];
    $table_fields = array_intersect_key($field_handlers, $columns);

    // Build form table with checkbox for each field.
    $form['table-row'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Display'),
        $this->t('Column name'),
      ],
      '#empty' => $this->t('Sorry, There are no items!'),
      '#attributes' => [
        'class' => ['table-column-preference-form'],
      ],
      '#attached' => [
        'library' => [
          'farm_ui_views/table_column_preference',
        ],
      ],
    ];

    // Build the table rows and columns.
    foreach ($table_fields as $field_id => $field_info) {
      $form['table-row'][$field_id]['enabled'] = [
        '#type' => 'checkbox',
        '#default_value' => TRUE,
        '#disabled' => $style->options['default'] == $field_id,
        '#attributes' => [
          'data-field-id' => $field_id,
        ],
      ];
      $form['table-row'][$field_id]['label'] = [
        '#markup' => !empty($field_info['label']) ? $field_info['label'] : $field_info['admin_label'],
      ];
    }

    // Reset button.
    // @TODO: Implement reset.
    $form['reset'] = [
      '#type' => 'submit',
      '#value'  => 'Reset',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
