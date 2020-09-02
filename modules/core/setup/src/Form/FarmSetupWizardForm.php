<?php

namespace Drupal\farm_setup\Form;

use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_setup\FarmSetupPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Setup Wizard Form.
 */
class FarmSetupWizardForm extends FormBase {

  /**
   * ClassResolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolver
   */
  protected $classResolver;

  /**
   * The FarmSetup plugin manager.
   *
   * This is used to get all of the FarmSetup plugins.
   *
   * @var \Drupal\farm_setup\FarmSetupPluginManager
   */
  protected $farmSetupManager;

  /**
   * An array of Setup Wizard form class names.
   *
   * @var array
   */
  protected $wizardForms;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolver $class_resolver
   *   Used to create instances of Form classes before calling their buildForm
   *   method.
   * @param \Drupal\farm_setup\FarmSetupPluginManager $farm_setup_manager
   *   The farm setup plugin manger service. We're injecting this service so
   *   that we can use it to access the FarmSetup plugins.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct(ClassResolver $class_resolver, FarmSetupPluginManager $farm_setup_manager) {
    $this->classResolver = $class_resolver;
    $this->farmSetupManager = $farm_setup_manager;

    // Get setup_wizard form definitions sorted by weight.
    $wizard_forms = $this->farmSetupManager->getFormClasses('setup_wizard');
    $this->wizardForms = array_column($wizard_forms, 'form_class');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_setup_wizard';
  }

  /**
   * Helper function to check if there are more steps in the setup wizard.
   *
   * @return int
   *   The number of setup wizard forms provided via FarmSetup plugins.
   */
  private function totalSteps() {
    return count($this->wizardForms);
  }

  /**
   * Helper function to load the form class of the specified wizard step.
   *
   * @param int $step_num
   *   The step number's form class to load.
   *
   * @return string
   *   Form class name.
   */
  private function getStepFormClass($step_num) {
    // Offset the step number by one when loading class from array.
    return $this->wizardForms[$step_num - 1];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // An array of submit handlers to remember.
    $submit_handlers = [];

    // Check if the setup wizard has been started.
    if (!$form_state->has('current_step')) {
      $form_state->set('current_step', 0);
    }

    // Load the current step.
    $current_step = $form_state->get('current_step');

    // Render the start form for the first step.
    if ($current_step == 0) {
      $form = $this->startForm($form, $form_state);
    }

    // Render a plugin's form if we are not on the first or last step.
    if ($current_step > 0 && $current_step <= $this->totalSteps()) {

      // Get the form class.
      $form_class = $this->getStepFormClass($current_step);

      // Use the class resolver to create an instance of the form class.
      // We must create an instance of the class before calling the buildForm
      // method because it is not static and the form may have other
      // dependencies. We dont use a FormBuilder here because we simply want
      // the forms render array without instantiating an entire new form state.
      $class = $this->classResolver->getInstanceFromDefinition($form_class);

      // Get the forms render array.
      $form = $class->buildForm($form, $form_state);

      // Collect submit handlers from the form class.
      if (method_exists($class, 'submitForm')) {
        $submit_handlers[] = [$class, 'submitForm'];
      }
    }

    // Build the finish form for the last step.
    if ($current_step > $this->totalSteps()) {
      $form = $this->finishForm($form, $form_state);
    }

    // Override the form actions element so that we can provide
    // "Back" and "Next" buttons in place of provided submit buttons.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Include the "Back" button on all but the first step.
    if ($current_step > 0) {
      $form['actions']['back'] = [
        '#type' => 'submit',
        '#button_type' => 'secondary',
        '#value' => $this->t('Back'),
        '#submit' => [$this, 'backSubmit'],
        '#weight' => -1,
      ];
    }

    // Include the "Next" button on all but the last step.
    if ($current_step <= $this->totalSteps()) {

      // Add nextSubmit to list of submit handlers.
      $submit_handlers[] = [$this, 'nextSubmit'];

      $form['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        '#submit' => $submit_handlers,
        '#weight' => 1,
      ];
    }

    return $form;
  }

  /**
   * Provides custom submission handler for page 1.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function nextSubmit(array &$form, FormStateInterface $form_state) {
    $current_step = $form_state->get('current_step');
    $form_state
      ->set('current_step', $current_step + 1)
      ->setRebuild(TRUE);
  }

  /**
   * Provides custom submission handler for page 1.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function backSubmit(array &$form, FormStateInterface $form_state) {
    $current_step = $form_state->get('current_step');

    // Don't go back past step 0.
    if ($current_step > 0) {
      $form_state->set('current_step', $current_step + 1);
    }

    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * Helper function to render the first step of the setup wizard.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  private function startForm(array $form, FormStateInterface $form_state) {

    $form['info'] = [
      '#markup' => '<p>' . $this->t('The setup wizard will guide you through the process of configuring your farmOS.') . '</p>',
    ];

    return $form;
  }

  /**
   * Helper function to render the last step of the setup wizard.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.y
   */
  private function finishForm(array $form, FormStateInterface $form_state) {

    $form['info'] = [
      '#markup' => '<p>' . $this->t('You have completed the setup wizard. You can revisit these settings at any time.') . '</p>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Override the parent method so that we can inject dependencies.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('class_resolver'),
      $container->get('plugin.manager.farm_setup')
    );
  }

}
