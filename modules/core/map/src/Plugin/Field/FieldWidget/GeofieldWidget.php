<?php

namespace Drupal\farm_map\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geocoder\Entity\GeocoderProvider;
use Drupal\geofield\Plugin\Field\FieldWidget\GeofieldBaseWidget;

/**
 * Plugin implementation of the map 'geofield' widget.
 *
 * @FieldWidget(
 *   id = "farm_map_geofield",
 *   label = @Translation("farmOS Map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class GeofieldWidget extends GeofieldBaseWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'geocode_file_field' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['geocode_file_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File field to geocode from.'),
      '#default_value' => $this->getSetting('geocode_file_field'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Wrap the map in a collapsible details element.
    $field_name = $this->fieldDefinition->getName();
    $field_wrapper_id = $field_name . '_wrapper';
    $element['#type'] = 'details';
    $element['#title'] = $this->t('Geometry');
    $element['#open'] = TRUE;
    $element['#prefix'] = '<div id="' . $field_wrapper_id . '">';
    $element['#suffix'] = '</div>';

    // Get the current form state value. Prioritize form state over field value.
    $form_value = $form_state->getValue([$field_name, $delta, 'value']);
    $field_value = $items[$delta]->value;
    $current_value = $form_value ?? $field_value;

    // Define the map render array.
    $element['map'] = [
      '#type' => 'farm_map',
      '#map_type' => 'geofield_widget',
      '#map_settings' => [
        'wkt' => $current_value,
        'behaviors' => [
          'wkt' => [
            'edit' => TRUE,
            'zoom' => TRUE,
          ],
        ],
      ],
    ];

    // Add a textarea for the WKT value.
    $element['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Geometry'),
      // @todo this is causing a bug.
      #'#value' => $current_value,
    ];

    // Add an option to geocode geometry using files field.
    // The "geocode_file_field" field setting must be configured and the
    // field must be included in the current form.
    $geocode_file_field = $this->getSetting('geocode_file_field');
    if (!empty($geocode_file_field) && !empty($form[$geocode_file_field])) {
      $element['trigger'] = [
        '#type' => 'submit',
        '#value' => $this->t('Find using files field'),
        '#submit' => [[$this, 'fileParse']],
        '#ajax' => [
          'wrapper' => $field_wrapper_id,
          'callback' => [$this, 'fileCallback'],
          'message' => $this->t('Working...'),
        ],
      ];
    }

    return $element;
  }

  /**
   * Submit function to parse geometries from uploaded files.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function fileParse(array &$form, FormStateInterface $form_state) {

    // Bail if no geocode file field is not configured.
    $geocode_file_field = $this->getSetting('geocode_file_field');
    if (empty($geocode_file_field)) {
      return;
    }

    // Get the form field element.
    $triggering_element = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));

    // Load the uploaded files.
    $uploaded_files = $form_state->getValue($geocode_file_field);
    if (!empty($uploaded_files)) {

      // Get file IDs.
      $file_ids = array_reduce($uploaded_files, function ($carry, $file) {
        return array_merge($carry, array_values($file['fids']));
      }, []);

      // Load and process each file.
      $providers = GeocoderProvider::loadMultiple();
      /** @var \Drupal\file\Entity\File[] $files */
      $files = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($file_ids);

      // @todo Support multiple files. Combine geometries?
      // @todo Support geometry field with > 1 cardinality.
      // Geocoder uses a single -> multiple vs multiple -> multiple setting.
      $wkt = '';
      foreach ($files as $file) {
        if ($geo_collection = \Drupal::service('geocoder')->geocode($file->getFileUri(), $providers)) {
          $wkt = $geo_collection->out('wkt');
        }
      }

      // Set the form state.
      $field_name = $this->fieldDefinition->getName();
      $delta = $element['#delta'];
      $form_state->setValue([$field_name, $delta, 'value'], $wkt);

      // Rebuild the form so the map widget is rebuilt with the new value.
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * AJAX callback for the find using files field button.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array|mixed|null
   *   The map form element to replace
   */
  public function fileCallback(array &$form, FormStateInterface $form_state) {
    // Return the rebuilt map form field field element.
    $triggering_element = $form_state->getTriggeringElement();
    return NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));
  }

}
