import $ from 'jquery';

import {
  replaceElement,
  createAddressSelection
} from './utils/select-address';

const PROVINCE_SELECTOR = 'select.js_field-state, [name="_billing_state"], [name="_shipping_state"]';
const DISTRICT_SELECTOR = '[name="_billing_city"], [name="_shipping_city"]';
const WARDS_SELECTOR = '[name="_billing_address_2"], [name="_shipping_address_2"]';

$(document.body).on('country-change.woocommerce', (e, country, wrapper) => {
  let inputType = country === 'VN' ? 'select' : 'input';

  replaceElement(wrapper.find(DISTRICT_SELECTOR)[0], inputType);
  replaceElement(wrapper.find(WARDS_SELECTOR)[0], inputType);

  if (inputType === 'select') {
    createAddressSelection({
      provinceElement: wrapper.find(PROVINCE_SELECTOR)[0],
      districtElement: wrapper.find(DISTRICT_SELECTOR)[0],
      wardElement: wrapper.find(WARDS_SELECTOR)[0]
    });
  }
});
