import { combineReducers } from '@wordpress/data';

const DEFAULT_ADDRESS_DATA = {
  provinces: []
};

const DEFAULT_CONFIG_DATA = {
  shipmentInfo: null,
  orderShippingMethods: [],
};

/**
 * Reducer managing the address data.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
const addressData = (state = DEFAULT_ADDRESS_DATA, action) => {
  switch (action.type) {
    case 'SET_PROVINCES_DATA':
      return {
        ...state,
        provinces: action.data
      };
  }

  return state;
};

/**
 * Reducer managing the config.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
const config = (state = DEFAULT_CONFIG_DATA, action) => {
  switch (action.type) {
    case 'SET_INITIAL_CONFIG':
      return { ...action.config };
    case 'SET_SHIPMENT_INFO':
      return {
        ...state,
        shippingData: action.data
      };
    case 'SET_CONFIG':
      return {
        ...state,
        [action.key]: action.value
      };
  }

  return state;
};

export default combineReducers({
  addressData,
  config
});
