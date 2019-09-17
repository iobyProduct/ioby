<?php

/**
 * @file
 * ioby general settings.
 */

/**
 * ioby settings form.
 */
function ioby_settings_form($form, &$form_state) {
  
  
  $form['project_page_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Project page settings'),
  );
  
  $gratuity_modes = array(
    IOBY_GRATUITY_MODE_NONE => t('- None -'), 
    IOBY_GRATUITY_MODE_AUTOMATIC => t('Automatically'), 
    IOBY_GRATUITY_MODE_MANUAL => t('Manual (on the Donate form)')
  );
  
  $form['project_page_settings']['ioby_gratuity_mode'] = array(
    '#type' => 'radios',
    '#title' => t('Gratuity mode'),
    '#default_value' => variable_get('ioby_gratuity_mode', IOBY_GRATUITY_MODE_AUTOMATIC),
    '#options' => $gratuity_modes,
  );
  
  $form['project_page_settings']['ioby_gratuity_checkbox_label'] = array(
    '#type' => 'textfield',
    '#title' => t('Gratuity checkbox label'),
    '#default_value' => variable_get('ioby_gratuity_checkbox_label', t('Add support for ioby.')),
    '#description' => t('"What\'s this?" popup link will be append to the label.'),
    '#states' => array(
      'visible' => array(
        ':input[name="ioby_gratuity_mode"]' => array('value' => IOBY_GRATUITY_MODE_MANUAL),
      ),
    ),
  );

  $form['project_page_settings']['ioby_gratuity_checkbox_default'] = array(
    '#type' => 'radios',
    '#title' => t('Gratuity checkbox default value'),
    '#default_value' => variable_get('ioby_gratuity_checkbox_default', 1),
    '#options' => array(1 => t('On'), 0 => t('Off')),
    '#states' => array(
      'visible' => array(
        ':input[name="ioby_gratuity_mode"]' => array('value' => IOBY_GRATUITY_MODE_MANUAL),
      ),
    ),
  );
  
  $form['project_page_settings']['ioby_checkout_button_text'] = array(
    '#type' => 'textfield',
    '#title' => t('Checkout button text'),
    '#default_value' => variable_get('ioby_checkout_button_text', t('Donate to this project')),
  );
  
  $options = array(
    'cart' => '/cart',
    'checkout' => '/checkout',
  );
  $form['project_page_settings']['ioby_checkout_button_redirect'] = array(
    '#type' => 'select',
    '#title' => t('Checkout button redirect'),
    '#default_value' => variable_get('ioby_checkout_button_redirect', 'cart'),
    '#options' => $options,
    '#description' => t('Note: if "Gratuity mode" is set to Automatically redirecting to /checkout will not add Gratuity because it is added on the /cart page.'),
  );

  return system_settings_form($form);
}
