<?php

namespace Drupal\age_checker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use \Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AgeCheckerAgeGate.
 *
 * @package Drupal\age_checker\Controller
 */
class AgeCheckerAgeGate extends ControllerBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a AgeCheckerAgeGate object
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   * @param \Drupal\Core\State\StateInterface $state
   */
  public function __construct(FormBuilderInterface $formBuilder, StateInterface $state) {
    $this->formBuilder = $formBuilder;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('state')
    );
  }

  /**
   * Function ageCheckerTemplate.
   */
  public function ageCheckerTemplate() {

    // Getting the language Code.
    $language_code = $this->ageCheckerGetLanguageCode();

    // Header text of the form.
    $age_checker_header_message_array = $this->state->get('age_checker_' . $language_code . '_age_gate_header');
    $age_checker_header_message = $age_checker_header_message_array['value'];

    // Form Element.
    $age_checker_form = $this->formBuilder->getForm('\Drupal\age_checker\Form\AgeCheckerForm');

    // Footer text of the form.
    $age_checker_footer_message_array = $this->state->get('age_checker_' . $language_code . '_age_gate_footer');
    $age_checker_footer_message = $age_checker_footer_message_array['value'];

    return array(
      '#theme' => 'age_checker',
      '#age_checker_header_message' => $age_checker_header_message,
      '#age_checker_form' => $age_checker_form,
      '#age_checker_footer_message' => $age_checker_footer_message,
    );
  }

  /**
   * Getting the language_code on the basis of Country selected.
   */
  public static function ageCheckerGetLanguageCode() {

    $languages_options = array();
    $countries_array = array();
    $languages = \Drupal::state()->get('age_checker_language', '');
    $languages = explode("\n", $languages);

    foreach ($languages as $language) {
      $language = explode('|', $language);
      $language = array_map('trim', $language);
      $languages_options[$language[0]] = isset($language[1]) ? $language[1] : NULL;
    }

    $selected_country = isset($_COOKIE['country_selected']) ? $_COOKIE['country_selected'] : age_checker_get_country_name();

    foreach ($languages_options as $key => $value) {
      $countries_array = \Drupal::state()->get('age_checker_' . $key . '_country_list');
      if (!empty($countries_array)) {
        foreach ($countries_array as $country) {
          if ($country == $selected_country) {
            return $key;
          }
        }
      }
    }
  }

}
