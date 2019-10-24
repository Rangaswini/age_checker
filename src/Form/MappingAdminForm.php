<?php

namespace Drupal\age_checker\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MappingAdminForm.
 *
 * @package Drupal\age_checker\Form
 */
class MappingAdminForm extends ConfigFormBase {
  /** The Key/Value Store to use for state.
   *
   * @var \Drupal\Core\State\StateInterface
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */

  protected  $configFactory;
  protected $state;

  /**
   * Constructs \Drupal\tint\Plugin\Derivative\TintEmbedBlockDerivative object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue store.
   */

  public function __construct(ConfigFactoryInterface $configFactory, StateInterface $state) {
    parent::__construct($configFactory);
    $this->state = $state;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'age_checker_mapping_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'age_checker_mapping.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $result = [];
    $config = $this->config('age_checker_mapping.settings');

    $form = array();
    $languages = $this->state->get('age_checker_language', 'Please provide values');
    $languages = explode("\n", $languages);

    foreach ($languages as $language) {
      $language = explode('|', $language);
      $language = array_map('trim', $language);
      $form[$language[0] . '_mapping'] = array(
        '#type' => 'fieldset',
        '#title' => Html::escape($language[1]),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      $language = $language[0];

      // Field for selecting the country for a particular language.
      $countries = $this->state->get('age_checker_countries') ? ($this->state->get('age_checker_countries')) : $this->config('age_checker.settings')->get('age_checker_countries');

      $countries = explode("\n", $countries);
      $country_options = array();
      $country_options[0] = 'Select the Country';
      foreach ($countries as $country) {
        $country = explode('|', $country);
        $country_options[$country[0]] = $country[1];
        $result = array_map('trim', $country_options);
      }

      // Label of Country Field.
      $form[$language . '_mapping']['age_checker_' . $language . '_select_list_label'] = array(
        '#type' => 'textfield',
        '#title' => Html::escape(t('Label for selecting country')),
        '#maxlength' => 255,
        '#required' => FALSE,
        '#default_value' => $config->get('age_checker_' . $language . '_select_list_label', 'Select your country'),
      );

      $form[$language . '_mapping']['age_checker_' . $language . '_country_list'] = array(
        '#title' => t('Select country(s)'),
        '#type' => 'select',
        '#description' => t('Please select the country(s) to map to this language'),
        '#default_value' => $config->get('age_checker_' . $language . '_country_list', ''),
        '#options' => $result,
        '#multiple' => TRUE,
      );

      // Message to be added before the form.
      $message_beforeform = $config->get('age_checker_' . $language . '_age_gate_header', array('value' => '', 'format' => NULL));
      $form[$language . '_mapping']['age_checker_' . $language . '_age_gate_header'] = array(
        '#title' => t('Header text for the form'),
        '#type' => 'text_format',
        '#rows' => 6,
        '#default_value' => isset($message_beforeform['value']) ? $message_beforeform['value'] : '',
        '#description' => t('e.g. You must be of legal drinking age to enter this site. Enter you date of birth below.'),
        '#format' => $message_beforeform['format'],
      );

      // Message to be added after the form.
      $message_afterform = $config->get('age_checker_' . $language . '_age_gate_footer', array('value' => '', 'format' => NULL));
      $form[$language . '_mapping']['age_checker_' . $language . '_age_gate_footer'] = array(
        '#title' => t('Cookie statement'),
        '#type' => 'text_format',
        '#rows' => 6,
        '#default_value' => isset($message_afterform['value']) ? $message_afterform['value'] : '',
        '#description' => t('e.g This website uses cookies that are stored on your computer in order to enhance your experience. By providing your date of birth, you also agree to the use of cookies.'),
        '#format' => $message_afterform['format'],
      );

      // Age checker validation message.
      $form[$language . '_mapping']['age_checker_' . $language . '_blank_error_msg'] = array(
        '#type' => 'textarea',
        '#title' => t('Blank Error Message'),
        '#required' => TRUE,
        '#maxlength' => 255,
        '#default_value' => $config->get('age_checker_' . $language . '_blank_error_msg') ? $config->get('age_checker_' . $language . '_blank_error_msg') : $config->get('age_checker_blank_error_msg'),
        '#description' => t('Enter a helpful and user-friendly message.'),
      );

      // Incorrect Date Format Validation Message.
      $form[$language . '_mapping']['age_checker_' . $language . '_dateformat_error_msg'] = array(
        '#type' => 'textarea',
        '#title' => t('Incorrect Date Format Message'),
        '#required' => TRUE,
        '#maxlength' => 255,
        '#default_value' => $config->get('age_checker_' . $language . '_dateformat_error_msg') ? $config->get('age_checker_' . $language . '_dateformat_error_msg') : $config->get('age_checker_dateformat_error_msg'),
        '#description' => t('Enter a helpful and user-friendly message.'),
      );
      // Date Out of Range Validation Message.
      $form[$language . '_mapping']['age_checker_' . $language . '_daterange_error_msg'] = array(
        '#type' => 'textarea',
        '#title' => t('Date Out Of Range Message'),
        '#required' => TRUE,
        '#maxlength' => 255,
        '#default_value' => $config->get('age_checker_' . $language . '_daterange_error_msg') ? $config->get('age_checker_' . $language . '_daterange_error_msg') : $config->get('age_checker_daterange_error_msg'),
        '#description' => t('Enter a helpful and user-friendly message.'),
      );

      // Error message for under age.
      $form[$language . '_mapping']['age_checker_' . $language . '_underage_error_msg'] = array(
        '#type' => 'textarea',
        '#title' => t('Under Age Validation Message'),
        '#required' => TRUE,
        '#maxlength' => 255,
        '#default_value' => $config->get('age_checker_' . $language . '_underage_error_msg') ? $config->get('age_checker_' . $language . '_underage_error_msg') : $config->get('age_checker_underage_error_msg'),
        '#description' => t('Enter a helpful and user-friendly message.'),
      );

      // Remember Me Text Configuration.
      $form[$language . '_mapping']['age_checker_' . $language . '_remember_me_text'] = array(
        '#type' => 'textfield',
        '#title' => t('Remember Me Text'),
        '#maxlength' => 255,
        '#required' => FALSE,
        '#default_value' => $config->get('age_checker_' . $language . '_remember_me_text') ? $config->get('age_checker_' . $language . '_remember_me_text') : $config->get('age_checker_remember_me_text'),
        '#description' => t('Please enter the remember me text.'),
      );

      // Age checker submit button text.
      $form[$language . '_mapping']['age_checker_' . $language . '_button_text'] = array(
        '#type' => 'textfield',
        '#title' => t('Label of submit button'),
        '#maxlength' => 50,
        '#required' => TRUE,
        '#default_value' => $config->get('age_checker_' . $language . '_button_text') ? $config->get('age_checker_' . $language . '_button_text') : $config->get('age_checker_button_text'),
        '#description' => t('Please enter submit button text.'),
      );

      // Configuration for adding Footer Links.
      $form[$language . '_mapping']['age_checker_' . $language . '_footer_links'] = array(
        '#type' => 'textarea',
        '#title' => t('Footer text and link'),
        '#required' => FALSE,
        '#maxlength' => 255,
        '#default_value' => $config->get('age_checker_' . $language . '_footer_links', ''),
        '#description' => t('Please enter footer links like Privacy policy, Terms of use, cookie policy in key|value format for e.g - Privacy Policy | http://site.link'),
      );

      // Copy right text.
      $copyright_text = $config->get('age_checker_' . $language . '_copyright', array('value' => '', 'format' => NULL));
      $form[$language . '_mapping']['age_checker_' . $language . '_copyright'] = array(
        '#type' => 'text_format',
        '#title' => t('Copyright text'),
        '#rows' => 6,
        '#default_value' => isset($copyright_text['value']) ? $copyright_text['value'] : '',
        '#description' => t('Copyright © 2015 Brand name. All rights reserved'),
        '#format' => $copyright_text['format'],
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements hook_form_submit().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $config = $this->config('age_checker_mapping.settings');
    $languages = $this->state->get('age_checker_language', 'Please provide values');
    $languages = explode("\n", $languages);
    foreach ($languages as $language) {
      $language = explode('|', $language);
      $language = array_map('trim', $language);
      $language = $language[0];

      $this->state->set('age_checker_' . $language . '_country_list', $values['age_checker_' . $language . '_country_list']);
      $this->state->set('age_checker_' . $language . '_age_gate_header', $values['age_checker_' . $language . '_age_gate_header']);
      $this->state->set('age_checker_' . $language . '_age_gate_footer', $values['age_checker_' . $language . '_age_gate_footer']);
      $this->state->set('age_checker_' . $language . '_blank_error_msg', $values['age_checker_' . $language . '_blank_error_msg']);
      $this->state->set('age_checker_' . $language . '_dateformat_error_msg', $values['age_checker_' . $language . '_dateformat_error_msg']);
      $this->state->set('age_checker_' . $language . '_daterange_error_msg', $values['age_checker_' . $language . '_daterange_error_msg']);
      $this->state->set('age_checker_' . $language . '_underage_error_msg', $values['age_checker_' . $language . '_underage_error_msg']);
      $this->state->set('age_checker_' . $language . '_remember_me_text', $values['age_checker_' . $language . '_remember_me_text']);
      $this->state->set('age_checker_' . $language . '_button_text', $values['age_checker_' . $language . '_button_text']);
      $this->state->set('age_checker_' . $language . '_footer_links', $values['age_checker_' . $language . '_footer_links']);
      $this->state->set('age_checker_' . $language . '_copyright', $values['age_checker_' . $language . '_copyright']);

      $this->config('age_checker_mapping.settings')
        ->set('age_checker_' . $language . '_select_list_label', $values['age_checker_' . $language . '_select_list_label'])
        ->set('age_checker_' . $language . '_country_list', $values['age_checker_' . $language . '_country_list'])
        ->set('age_checker_' . $language . '_age_gate_header', $values['age_checker_' . $language . '_age_gate_header'])
        ->set('age_checker_' . $language . '_age_gate_footer', $values['age_checker_' . $language . '_age_gate_footer'])
        ->set('age_checker_' . $language . '_blank_error_msg', $values['age_checker_' . $language . '_blank_error_msg'])
        ->set('age_checker_' . $language . '_dateformat_error_msg', $values['age_checker_' . $language . '_dateformat_error_msg'])
        ->set('age_checker_' . $language . '_daterange_error_msg', $values['age_checker_' . $language . '_daterange_error_msg'])
        ->set('age_checker_' . $language . '_underage_error_msg', $values['age_checker_' . $language . '_underage_error_msg'])
        ->set('age_checker_' . $language . '_remember_me_text', $values['age_checker_' . $language . '_remember_me_text'])
        ->set('age_checker_' . $language . '_button_text', $values['age_checker_' . $language . '_button_text'])
        ->set('age_checker_' . $language . '_footer_links', $values['age_checker_' . $language . '_footer_links'])
        ->set('age_checker_' . $language . '_copyright', $values['age_checker_' . $language . '_copyright'])
        ->save();
    }
    // Set values in variables.
    parent::submitForm($form, $form_state);
  }

}
