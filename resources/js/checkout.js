import $ from 'jquery';

import {
  replaceElement,
  createAddressSelection
} from './utils/select-address';

const PROVINCE_SELECTOR = 'select.state_select, [name="billing_state"], [name="shipping_state"]';
const DISTRICT_SELECTOR = '.address-field [name="billing_city"], .address-field [name="shipping_city"]';
const WARD_SELECTOR = '.address-field [name="billing_address_2"], .address-field [name="shipping_address_2"]';

/**
 * @param {jQuery.Event} e
 * @param {Object} country
 * @param {jQuery} $wrapper
 */
function onCountryChange(e, country, $wrapper) {
  let inputType = country === 'VN' ? 'select' : 'input';

  replaceElement($wrapper.find(DISTRICT_SELECTOR)[0], inputType);
  replaceElement($wrapper.find(WARD_SELECTOR)[0], inputType);

  if (inputType === 'select') {
    createAddressSelection({
      provinceElement: $wrapper.find(PROVINCE_SELECTOR)[0],
      districtElement: $wrapper.find(DISTRICT_SELECTOR)[0],
      wardElement: $wrapper.find(WARD_SELECTOR)[0]
    });
  }
}

$(document.body).on('country_to_state_changed', onCountryChange);
