<?php
namespace Drupal\validate_staff\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class GrouperMap extends ConfigFormBase {

  const SETTINGS = 'validate_staff.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'grouper-map-form';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config(static::SETTINGS);

    $grouperMap = $config->get('grouper_map');

    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();

    $rolesMarkup = '';
    foreach ($roles as $role) {
      $rolesMarkup .= '<li>' . $role->id() . '</li>';
    }

    $form['drupal_roles'] = [
      '#type' => 'item',
      '#markup' => '<ul>' . $rolesMarkup . '</ul>',
      '#title' => t('Drupal Roles'),
    ];

    $form['grouper_map'] = [
      '#type' => 'textarea',
      '#title' => t('Grouper Mappings'),
      '#description' => t('One mapping per line with the format <strong>Grouper Group|Drupal Role</strong>. Note that the Grouper groups will be changed to lowercase.'),
      '#default_value' => $this->allowedValuesString($grouperMap),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $grouperMap = $this->extractAllowedValues($form_state->getValue('grouper_map'));

    $this->configFactory->getEditable(static::SETTINGS)->set('grouper_map', $grouperMap)->save();
    parent::submitForm($form, $form_state);

  }

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  protected function allowedValuesString($values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = strtolower(trim($matches[1]));
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    return $values;
  }

}
