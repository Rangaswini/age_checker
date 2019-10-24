<?php

namespace Drupal\age_checker\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class SettingsForm.
 *
 * @package Drupal\age_checker\Form
 */
class SettingsForm extends ConfigFormBase implements ConfigFactoryInterface{
  /** The Key/Value Store to use for state.
   *
   * @var \Drupal\Core\State\StateInterface
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */

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
    return 'age_checker_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'age_checker.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('age_checker.settings');

    $form = array();

    // Remember me checkbox for Age Gate.
    $form['age_checker_option_remember_me'] = array(
      '#type' => 'checkbox',
      '#title' => t('Would you like to display remember me check box'),
      '#default_value' => $config->get('age_checker_option_remember_me', 0),
      '#description' => t('If this checkbox is enabled, a remember me checkbox would be seen on the Age gate page.'),
    );

    // URL from where we need to get the Country code.
    $form['age_checker_country_code_url'] = array(
      '#title' => t('URL for fetching the country code'),
      '#type' => 'textfield',
      '#default_value' => $config->get('age_checker_country_code_url', 'http://geoip.nekudo.com/api/'),
      '#description' => t('API for fetching the country code.'),
    );

    // Language list for Age Gate.
    $form['lang'] = array(
      '#title' => t('Age Checker Language'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['lang']['age_checker_language'] = array(
      '#type' => 'textarea',
      '#title' => t('Please enter the list of languages in key|value pair.'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $config->get('age_checker_language'),
      '#description' => t('Please enter required language in key|value format for e.g. es|Español. The name of the key should not have any space.'),
    );

    // Country list for Age Gate.
    $form['country'] = array(
      '#title'         => t('Age Checker Countries'),
      '#type'          => 'fieldset',
      '#collapsible'   => TRUE,
      '#collapsed'     => TRUE,
    );

    $url = Url::fromUri('http://www.worldatlas.com/aatlas/ctycodes.htm');
    $link_options = array(
      'attributes' => array(
        'class' => array(
          'sample_link',
        ),
      ),
    );
    $url->setOptions($link_options);
    $link = Link::fromTextAndUrl(t('http://www.worldatlas.com/aatlas/ctycodes.htm'), $url)->toString();

    $form['country']['age_checker_countries'] = array(
      '#type' => 'textarea',
      '#title' => t('Please enter the list of countries in key|value pair.'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $config->get('age_checker_countries'),
      '#description' => t('Please enter required country in localized language e.g. ES|España. The key should be picked up from A2 (ISO) column of %link site depending on the value of the country.', array('%link' => $link)),
    );

    // Verification options for age checker.
    $form['options'] = array(
      '#title' => t('Age Checker Options'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    // Cookie Expiration Time.
    $form['options']['age_checker_cookie_expiration_time'] = array(
      '#title' => t('Cookie expiration days'),
      '#type' => 'textfield',
      '#field_suffix' => t('Days'),
      '#size' => 6,
      '#default_value' => $config->get('age_checker_cookie_expiration_time'),
      '#description' => t('The number of days before the cookie set by age checker module expires, and the user must verify their age again (0 days will expire at end of session).'),
    );

    // Age Checker URL.
    $form['options']['age_checker_under_age_url'] = array(
      '#title' => t('Enter underage page url'),
      '#type' => 'textfield',
      '#default_value' => $config->get('age_checker_under_age_url'),
      '#required' => TRUE,
      '#description' => t('Please add http:// or https:// for external url  or create a drupal CMS page and enter Drupal path for internal CMS Page. E.g "under-age" for  http://www.example.com/sitename/under-age'),
    );

    // Age Checker Visibility.
    $form['options']['age_checker_visibility'] = array(
      '#type' => 'radios',
      '#title' => t('Show Age Gate on specific pages'),
      '#options' => array(
        AGE_CHECKER_VISIBILITY_NOTLISTED => t('Show on all pages except those listed'),
        AGE_CHECKER_VISIBILITY_LISTED    => t('Show only on the listed pages'),
      ),
      '#default_value' => $config->get('age_checker_visibility'),
    );

    // Age checker specific pages.
    $form['options']['age_checker_pages'] = array(
      '#type' => 'textarea',
      '#title' => t('Age gate exception pages'),
      '#default_value' => $config->get('age_checker_pages'),
      '#description' => t("Enter the path of the page e.g. enter 'blog' for the blog main page and 'blog/*' for the blog main page and its subpages."),
    );

    // Background Image for Age Gate.
    $form['options']['age_checker_background_image'] = array(
      '#type' => 'managed_file',
      '#name' => 'backgroundimage_image',
      '#title' => t('Add Background image'),
      '#default_value' => $config->get('age_checker_background_image'),
      '#description' => t("Upload an image for the background of Age Gate. Allowed Extensions for the Image are gif, png, jpg, jpeg"),
      '#upload_location' => 'public://images_age_checker/',
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        'file_validate_size' => array(1000000),
      ),
    );

    // Logo Image for Age Gate.
    $form['options']['age_checker_logo'] = array(
      '#type' => 'managed_file',
      '#name' => 'logo_agechecker',
      '#title' => t('Add logo image'),
      '#default_value' => $config->get('age_checker_logo'),
      '#description' => t("Upload an image for the logo of Age Gate. Allowed Extensions for the Image are gif, png, jpg, jpeg"),
      '#upload_location' => 'public://images_age_checker/',
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        'file_validate_size' => array(1000000),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $countries_list = [];
    $language_list = [];

    // Validation for countries.
    $countries = $form_state->getValue('age_checker_countries');
    $countries = explode("\n", $countries);
    $country_options = array();
    foreach ($countries as $country) {
      $country = explode('|', $country);
      if ($country[1] == '') {
        $form_state->setErrorByName('age_checker_countries', $this->t('Please remove the extra space.'));
      }
      $country_options[$country[0]] = $country[1];
      $countries_list = array_map('trim', $country_options);
    }
    foreach ($countries_list as $country_list) {
      if (preg_match('/[0-9]/', $country_list)) {
        $form_state->setErrorByName('age_checker_countries', $this->t('Please enter proper country name'));
      }
    }

    // Validation for languages.
    $languages = $form_state->getValue('age_checker_language');
    $languages = explode("\n", $languages);
    foreach ($languages as $language) {
      $language = explode('|', $language);
      if ($language[1] == '') {
        $form_state->setErrorByName('age_checker_language', $this->t('Please remove the extra space.'));
      }
      $language_options[$language[0]] = $language[1];
      $language_list = array_map('trim', $language_options);
    }
    foreach ($language_list as $language) {
      if (preg_match('/[0-9]/', $language)) {
        $form_state->setErrorByName('age_checker_language', $this->t('Please enter proper language name'));
      }
    }
  }

  /**
   * Implements hook_form_submit().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set values in variables.
    $values = $form_state->getValues();
    $this->state->set('age_checker_option_remember_me', $values['age_checker_option_remember_me']);
    $this->state->set('age_checker_country_code_url', $values['age_checker_country_code_url']);
    $this->state->set('age_checker_language', $values['age_checker_language']);
    $this->state->set('age_checker_countries', $values['age_checker_countries']);
    $this->state->set('age_checker_cookie_expiration_time', $values['age_checker_cookie_expiration_time']);
    $this->state->set('age_checker_under_age_url', $values['age_checker_under_age_url']);
    $this->state->set('age_checker_visibility', $values['age_checker_visibility']);
    $this->state->set('age_checker_pages', $values['age_checker_pages']);
    $this->state->set('age_checker_background_image', $values['age_checker_background_image']);
    $this->state->set('age_checker_logo', $values['age_checker_logo']);

    $this->config('age_checker.settings')
      ->set('age_checker_option_remember_me', $values['age_checker_option_remember_me'])
      ->set('age_checker_country_code_url', $values['age_checker_country_code_url'])
      ->set('age_checker_language', $values['age_checker_language'])
      ->set('age_checker_countries', $values['age_checker_countries'])
      ->set('age_checker_cookie_expiration_time', $values['age_checker_cookie_expiration_time'])
      ->set('age_checker_under_age_url', $values['age_checker_under_age_url'])
      ->set('age_checker_visibility', $values['age_checker_visibility'])
      ->set('age_checker_pages', $values['age_checker_pages'])
      ->set('age_checker_background_image', $values['age_checker_background_image'])
      ->set('age_checker_logo', $values['age_checker_logo'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Returns an immutable configuration object for a given name.
   *
   * @param string $name
   *   The name of the configuration object to construct.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   A configuration object.
   */
  public function get($name) {
    // TODO: Implement get() method.
  }

  /**
   * Returns an mutable configuration object for a given name.
   *
   * Should not be used for config that will have runtime effects. Therefore it
   * is always loaded override free.
   *
   * @param string $name
   *   The name of the configuration object to construct.
   *
   * @return \Drupal\Core\Config\Config
   *   A configuration object.
   */
  public function getEditable($name) {
    // TODO: Implement getEditable() method.
  }

  /**
   * Returns a list of configuration objects for the given names.
   *
   * This will pre-load all requested configuration objects does not create
   * new configuration objects. This method always return immutable objects.
   * ConfigFactoryInterface::getEditable() should be used to retrieve mutable
   * configuration objects, one by one.
   *
   * @param array $names
   *   List of names of configuration objects.
   *
   * @return \Drupal\Core\Config\ImmutableConfig[]
   *   List of successfully loaded configuration objects, keyed by name.
   */
  public function loadMultiple(array $names) {
    // TODO: Implement loadMultiple() method.
  }

  /**
   * Resets and re-initializes configuration objects. Internal use only.
   *
   * @param string|null $name
   *   (optional) The name of the configuration object to reset. If omitted, all
   *   configuration objects are reset.
   *
   * @return $this
   */
  public function reset($name = NULL) {
    // TODO: Implement reset() method.
  }

  /**
   * Renames a configuration object using the storage.
   *
   * @param string $old_name
   *   The old name of the configuration object.
   * @param string $new_name
   *   The new name of the configuration object.
   *
   * @return $this
   */
  public function rename($old_name, $new_name) {
    // TODO: Implement rename() method.
  }

  /**
   * The cache keys associated with the state of the config factory.
   *
   * All state information that can influence the result of a get() should be
   * included. Typically, this includes a key for each override added via
   * addOverride(). This allows external code to maintain caches of
   * configuration data in addition to or instead of caches maintained by the
   * factory.
   *
   * @return array
   *   An array of strings, used to generate a cache ID.
   */
  public function getCacheKeys() {
    // TODO: Implement getCacheKeys() method.
  }

  /**
   * Clears the config factory static cache.
   *
   * @return $this
   */
  public function clearStaticCache() {
    // TODO: Implement clearStaticCache() method.
  }/**
 * Gets configuration object names starting with a given prefix.
 *
 * @param string $prefix
 *   (optional) The prefix to search for. If omitted, all configuration object
 *   names that exist are returned.
 *
 * @return array
 *   An array containing matching configuration object names.
 * @see \Drupal\Core\Config\StorageInterface::listAll()
 *
 */
  public function listAll($prefix = '') {
    // TODO: Implement listAll() method.
  }

  /**
   * Adds config factory override services.
   *
   * @param \Drupal\Core\Config\ConfigFactoryOverrideInterface $config_factory_override
   *   The config factory override service to add. It is added at the end of the
   *   priority list (lower priority relative to existing ones).
   */
  public function addOverride(ConfigFactoryOverrideInterface $config_factory_override) {
    // TODO: Implement addOverride() method.
  }

}
