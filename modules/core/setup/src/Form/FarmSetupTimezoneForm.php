<?php

namespace Drupal\farm_setup\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Timezone form.
 */
class FarmSetupTimezoneForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_setup_timezone';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'system.date',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Set the form title.
    $form['#title'] = $this->t('Configure timezone');

    // Get list of timezones.
    $timezones = system_time_zones();

    // Get the current default timezone.
    $date = $this->config('system.date');
    $default_timezone = $date->get('timezone')['default'];

    // Dropdown to select default timezone.
    $form['default_timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Default timezone'),
      '#description' => $this->t('The default timezone of the farmOS server. Note that users can configure individual timezones later.'),
      '#options' => $timezones,
      '#default_value' => $default_timezone,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the submitted timezone.
    $default_timezone = $form_state->getValue('default_timezone');

    // Update the default timezone config value.
    $this->configFactory->getEditable('system.date')
      ->set('timezone.default', $default_timezone)
      ->save();

    // Add message.
    $this->messenger()->addMessage($this->t('Default timezone set to') . ' "' . $default_timezone . '".');
  }

}
