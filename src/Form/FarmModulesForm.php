<?php

namespace Drupal\farm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for selecting farmOS modules to install.
 *
 * @ingroup farm
 */
class FarmModulesForm extends FormBase {

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new FarmModulesForm.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue collection to use.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_modules_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Set the form title.
    $form['#title'] = $this->t('Enable modules');

    // Load module options.
    $modules = $this->moduleOptions();

    // Module checkboxes.
    $form['modules'] = [
      '#title' => $this->t('farmOS Modules'),
      '#title_display' => 'invisible',
      '#type' => 'checkboxes',
      '#description' => $this->t('Select the farmOS modules that you would like installed by default.'),
      '#options' => $modules['options'],
      '#default_value' => $modules['default'],
    ];

    // Disable checkboxes for modules marked as disabled.
    foreach ($modules['disabled'] as $name) {
      $form['modules'][$name]['#disabled'] = TRUE;
    }

    // Submit button.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Helper function for building a list of modules to install.
   *
   * @return array
   *   Returns an array with three sub-arrays: 'options', 'default' and
   *   'disabled'. All modules should be included in the 'options' array.
   *   Default modules will be selected for installation by default, and
   *   disabled modules cannot have their checkbox changed by users.
   */
  protected function moduleOptions() {

    // Load the list of available modules.
    $modules = farm_modules();

    // Allow user to choose which high-level farm modules to install.
    $module_options = array_merge($modules['default'], $modules['optional']);

    // Default modules will be selected by default.
    $module_defaults = array_keys($modules['default']);

    return [
      'options' => $module_options,
      'default' => $module_defaults,
      'disabled' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $modules = array_filter($form_state->getValue('modules'));
    $this->state->set('farm.install_modules', $modules);
  }

}
