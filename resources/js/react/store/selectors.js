/**
 * Returns the address provinces.
 *
 * @param {Object} state
 * @return {Array}
 */
export const getProvinces = (state) => state.addressData.provinces;

/**
 * Returns the config.
 *
 * @param {Object} state
 * @return {Object}
 */
export const getConfig = (state) => state.config;

/**
 * Returns the shipping data.
 *
 * @param {Object} state
 * @return {Object|null}
 */
export const getShipmentInfo = (state) => state.config.shipmentInfo || null;
