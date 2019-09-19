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
  
  $form['project_page_settings']['ioby_gratuity_label'] = array(
    '#type' => 'fieldset',
    '#title' => t('Gratuity'),
  );
  
  $form['project_page_settings']['ioby_gratuity_label']['ioby_gratuity_checkbox_label'] = array(
    '#type' => 'textfield',
    '#title' => t('Checkbox label'),
    '#default_value' => variable_get('ioby_gratuity_checkbox_label', t('Add support for ioby.')),
    '#states' => array(
      'visible' => array(
        ':input[name="ioby_gratuity_mode"]' => array('value' => IOBY_GRATUITY_MODE_MANUAL),
      ),
    ),
  );
  
  $form['project_page_settings']['ioby_gratuity_label']['ioby_gratuity_checkbox_link'] = array(
    '#type' => 'textfield',
    '#title' => t('Checkbox Link text'),
    '#default_value' => variable_get('ioby_gratuity_checkbox_link', t("What\'s this?")),
    '#description' => t('Leave empty for disable it.'),
    '#states' => array(
      'visible' => array(
        ':input[name="ioby_gratuity_mode"]' => array('value' => IOBY_GRATUITY_MODE_MANUAL),
      ),
    ),
  );

  $form['project_page_settings']['ioby_gratuity_label']['ioby_gratuity_checkbox_default'] = array(
    '#type' => 'radios',
    '#title' => t('Checkbox default value'),
    '#default_value' => variable_get('ioby_gratuity_checkbox_default', 1),
    '#options' => array(1 => t('On'), 0 => t('Off')),
    '#states' => array(
      'visible' => array(
        ':input[name="ioby_gratuity_mode"]' => array('value' => IOBY_GRATUITY_MODE_MANUAL),
      ),
    ),
  );
  
  $form['project_page_settings']['ioby_gratuity_label']['ioby_gratuity_cart_label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label in the cart'),
    '#default_value' => variable_get('ioby_gratuity_cart_label', t('ioby gratuity from order')),
  );
  
  $form['project_page_settings']['ioby_gratuity_label']['ioby_gratuity_cart_link'] = array(
    '#type' => 'textfield',
    '#title' => t('Link text in the cart'),
    '#default_value' => variable_get('ioby_gratuity_cart_link', t("What\'s this?")),
    '#description' => t('Leave empty for disable it.'),
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
