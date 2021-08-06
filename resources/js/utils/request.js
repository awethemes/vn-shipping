import $ from 'jquery';

let cachedResponse = {};

/**
 * @param {Array} data
 * @returns {{label: *, value: *}[]}
 */
const castOptions = (data) => {
  if (!Array.isArray(data)) {
    return [];
  }

  return Array.from(data).map(
    ({ code: value, name_with_type: label }) => ({ value, label })
  );
};

/**
 * @param url
 * @param data
 * @param method
 * @returns {Promise<*>}
 */
export async function request(url, data, method = 'POST') {
  try {
    return await $.ajax({
      url,
      data,
      type: method,
      dataType: 'json',
      processData: !(data instanceof FormData),
      contentType: (data instanceof FormData)
        ? false
        : 'application/x-www-form-urlencoded; charset=UTF-8'
    });
  } catch (e) {
    if (e.responseJSON && e.responseJSON.message) {
      console.error(e);
    }

    return null;
  }
}

/**
 * Get the provinces.
 *
 * @returns {Promise}
 */
export async function getProvince() {
  const data = window._vnsOrderData || {};

  if (data.hasOwnProperty('provinces') && data.provinces) {
    return castOptions(data.provinces);
  }

  const response = await request(
    '/wp-json/awethemes/vn-shipping/address',
    null,
    'GET'
  );

  return castOptions(response);
}

/**
 * Get districts of a province.
 *
 * @param {number} province
 * @returns {Promise}
 */
export async function getDistrict(province) {
  const cache = `province_${province}`;

  if (cachedResponse.hasOwnProperty(cache)) {
    return castOptions(cachedResponse[cache]);
  }

  let response = await request(
    `/wp-json/awethemes/vn-shipping/address/${province}`,
    null,
    'GET'
  );

  if (Array.isArray(response) && response.length > 0) {
    cachedResponse[cache] = response;

    return castOptions(response);
  }

  return [];
}

/**
 * Get wards of a district.
 *
 * @param {String} district
 * @param {String} province
 * @returns {Promise}
 */
export async function getWards(district, province) {
  const cache = `district_${district}`;

  if (cachedResponse.hasOwnProperty(cache)) {
    return castOptions(cachedResponse[cache]);
  }

  let response = await request(
    `/wp-json/awethemes/vn-shipping/address/${province}/${district}`,
    null,
    'GET'
  );

  if (Array.isArray(response) && response.length > 0) {
    cachedResponse[cache] = response;

    return castOptions(response);
  }

  return [];
}
