<?php

namespace Drupal\farm_setup\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a FarmSetup annotation object.
 *
 * FarmSetup plugins can define settings forms to display in the farmOS
 * settings page and setup wizard.
 *
 * @Annotation
 */
class FarmSetup extends Plugin {
  /**
   * An ID unique for the FarmSetup plugin type.
   *
   * This ID will also be used in route paths to forms provided by plugins.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $id;

  /**
   * The label associated with the plugin's forms.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The weight of the plugin's setup forms.
   *
   * Defaults to 10.
   *
   * @var int
   */
  public $weight = 10;

}
