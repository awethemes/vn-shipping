import { find } from 'lodash';
import { reactive } from 'vue';
import CurrencyFactory from '../utils/currency';

const initialStates = window._vnShippingInitialStates || {};

const initialOrderStates = {
  selectedCourier: '',
  orderShippingInfo: null
};

export const store = {
  states: reactive({
    orderId: 0,
    orderShippingData: null,
    orderShippingMethods: [],
    availableCouriers: [],
    ...initialStates,
    ...initialOrderStates
  }),

  setShippingData(shippingInfo) {
    this.states.orderShippingData = shippingInfo;
  },

  setSelectedCourier(selectedCourier) {
    this.states.selectedCourier = selectedCourier;
  },

  setOrderShippingInfo(orderShippingInfo) {
    this.states.orderShippingInfo = orderShippingInfo;
  },

  getCourierInfo(name) {
    const maps = {
      'vtp': 'viettel_post',
      'ghn': 'giao_hang_nhanh',
      'ghtk': 'giao_hang_tiet_kiem'
    };

    if (maps.hasOwnProperty(name)) {
      name = maps[name];
    }

    return find(this.states.availableCouriers, { id: name });
  }
};

export const currency = new CurrencyFactory({
  code: 'VND',
  symbol: 'đ',
  symbolPosition: 'right',
  thousandSeparator: ',',
  decimalSeparator: '.',
  precision: 0
});
