import $ from 'jquery';

import * as api from './request';
import { createSelect2 } from './select2';

/**
 * @param {HTMLInputElement|*} currentElement
 * @param {String} type
 * @returns {HTMLInputElement|void}
 */
export function replaceElement(currentElement, type = 'select') {
  if (!currentElement) {
    return;
  }

  let currentValue = currentElement.value;

  const inputId = currentElement.getAttribute('id');
  const inputName = currentElement.getAttribute('name');
  const inputPlaceholder = currentElement.getAttribute('placeholder');

  if ($(currentElement).hasClass('select2-hidden-accessible')) {
    $(currentElement).selectWoo('destroy');
  }

  // Remove the previous DOM element.
  $(currentElement).parent()
    .find('.select2-container')
    .remove();

  let newElement;

  if ('select' === type) {
    newElement = $('<select class="select"></select>')
      .prop('id', inputId)
      .prop('name', inputName)
      .prop('placeholder', inputPlaceholder || '')
      .attr('data-o-value', currentValue);

    // Hold the current value.
    if (currentValue) {
      newElement.append(new Option('', currentValue, true));
      newElement.val(currentValue);
    }
  } else {
    newElement = $('<input type="text" class="input-text " />')
      .prop('id', inputId)
      .prop('name', inputName)
      .prop('placeholder', inputPlaceholder || '')
      .val(currentValue);
  }

  $(currentElement).replaceWith(newElement);

  return newElement;
}

/**
 * @param {HTMLSelectElement} provinceElement
 * @param {HTMLSelectElement} districtElement
 * @param {HTMLSelectElement} wardElement
 */
export function createAddressSelection({ provinceElement, districtElement, wardElement }) {
  if (!provinceElement || !districtElement) {
    return;
  }

  const [province, district, wards] = [
    createSelect2(provinceElement),
    createSelect2(districtElement),
    createSelect2(wardElement)
  ];

  const updateDistrict = async (defaultValue) => {
    if (!province.value()) {
      return;
    }

    district.disable();
    wards && wards.disable();

    const options = await api.getDistrict(province.value());
    district.setOptions(options, defaultValue);

    district.disable(false);
    wards && wards.disable(false);
  };

  const updateWard = async (defaultValue) => {
    if (!district.value() || !province.value()) {
      return;
    }

    wards.disable();
    const options = await api.getWards(district.value(), province.value());
    wards.disable(false);

    wards.setOptions(options, defaultValue);
  };

  updateDistrict(districtElement.getAttribute('data-o-value'))
    .then(() => {
      if (wards) {
        updateWard(wardElement.getAttribute('data-o-value'));
      }
    });

  $(provinceElement).on('change', () => {
    district.clearOptions();

    if (wards) {
      wards.clearOptions();
    }

    updateDistrict().then(() => {
      if (wards) {
        updateWard();
      }
    });
  });

  $(districtElement).on('change', () => {
    if (wards) {
      wards.clearOptions();
      updateWard();
    }
  });

  return () => {
    district.destroy();

    if (wards) {
      wards.destroy();
    }
  };
}
