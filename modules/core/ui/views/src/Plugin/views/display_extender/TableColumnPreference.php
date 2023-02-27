<?php

namespace Drupal\farm_ui_views\Plugin\views\display_extender;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;

/**
 * Defines a display extender plugin to configure collapsible exposed filters.
 *
 * @ViewsDisplayExtender(
 *   id = "table_column_preference",
 *   title = @Translation("Table column preference")
 * )
 */
class TableColumnPreference extends DisplayExtenderPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['table_column_preference'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    // Only extend the table style.
    $plugin_id = $this->view->getStyle()->getPluginId();
    if ($plugin_id != 'table') {
      return;
    }

    // Add form to the view style_options section.
    switch ($form_state->get('section')) {
      case 'style_options':
        $form['table_column_preference'] = [
          '#title' => $this->t('Table column preference'),
          '#type' => 'checkbox',
          '#description' => $this->t('Display exposed filters in a collapsible details element.'),
          '#default_value' => $this->options['table_column_preference'],
          '#weight' => -50,
        ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {

    // Only extend the table style.
    $plugin_id = $this->view->getStyle()->getPluginId();
    if ($plugin_id != 'table') {
      return;
    }

    parent::submitOptionsForm($form, $form_state);
    switch ($form_state->get('section')) {
      case 'style_options':
        $this->options['table_column_preference'] = $form_state->getValue('table_column_preference');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return ['farm_ui_views'];
  }

}
