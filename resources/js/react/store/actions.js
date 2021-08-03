import { castArray } from 'lodash';

export function setProvinceData(provinces) {
  return {
    type: 'SET_PROVINCES_DATA',
    data: castArray(provinces)
  };
}

export function setInitialConfig(config) {
  return {
    type: 'SET_INITIAL_CONFIG',
    config
  };
}

export function setShipmentInfo(data) {
  return {
    type: 'SET_SHIPMENT_INFO',
    data
  };
}

export function setConfig(key, value) {
  return {
    type: 'SET_CONFIG',
    key,
    value
  };
}
