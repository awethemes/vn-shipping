import moment from 'moment';
import apiFetch from '@wordpress/api-fetch';
import { dateI18n } from '@wordpress/date';
import { addQueryArgs } from '@wordpress/url';

import { store, currency } from './store';

export const InteractsWithAPI = {
  data() {
    return {
      isRequesting: {}
    };
  },

  methods: {
    /**
     * @param  {String} name
     * @returns {boolean}
     */
    isLoading(name) {
      return Boolean(this.isRequesting[name] || false);
    },

    /**
     * @returns {Promise}
     */
    getOrderShippingInfo() {
      const path = addQueryArgs('awethemes/vn-shipping/shipping/show', {
        order_id: this.currentOrderId
      });

      this.isRequesting['getOrderShippingInfo'] = true;

      return apiFetch({ path }).finally(() => {
        this.isRequesting['getOrderShippingInfo'] = false;
      });
    },

    /**
     * @param courier
     * @param data
     * @returns {Promise}
     */
    createShippingOrder(courier, data) {
      const path = `/awethemes/vn-shipping/shipping/${courier}/create`;
      data = { order_id: postId, ...data };

      this.isRequesting['getAvailableServices'] = true;

      return apiFetch({ method: 'POST', path, data }).finally(() => {
        this.isRequesting['getAvailableServices'] = false;
      });
    },

    /**
     * @param courier
     * @param data
     * @returns {Promise}
     */
    getAvailableServices(courier, data) {
      const path = `/awethemes/vn-shipping/shipping/${courier}/available-services`;
      data = { order_id: this.currentOrderId, ...data };

      this.isRequesting['getAvailableServices'] = true;

      return apiFetch({ method: 'POST', path, data }).finally(() => {
        this.isRequesting['getAvailableServices'] = false;
      });
    },

    /**
     * @param courier
     * @param data
     * @returns {Promise}
     */
    getShippingFee(courier, data) {
      const path = `/awethemes/vn-shipping/shipping/${courier}/fee`;

      data = { order_id: this.currentOrderId, ...data };

      return apiFetch({ method: 'POST', path, data });
    },

    /**
     * @param courier
     * @param data
     * @returns {Promise}
     */
    getLeadTime(courier, data) {
      const path = `/awethemes/vn-shipping/shipping/${courier}/lead-time`;

      data = { order_id: this.currentOrderId, ...data };

      return apiFetch({ method: 'POST', path, data });
    }
  },

  computed: {
    currentOrderId() {
      return this.states?.orderId || store.states?.orderId || 0;
    }
  }
};

export const InteractsWithCreateOrder = {
  props: [
    'courier',
    'orderShippingInfo'
  ],

  data() {
    const shipping = this.orderShippingInfo.shipping || {};

    return {
      name: shipping.name || null,
      phone: shipping.phone || null,
      address: shipping.address || null,
      address_data: shipping.address_data || null,
      cod: shipping.cod || null,
      insurance: shipping.insurance || null,
      width: shipping.width || null,
      height: shipping.height || null,
      length: shipping.length || null,
      weight: shipping.weight || null,
      note: shipping.note || null
    };
  }
};

export const FormattingMixin = {
  methods: {
    /**
     * @param {Number} number
     * @returns {?string}
     */
    formatCurrency(number) {
      return currency.formatAmount(number);
    },

    /**
     * @param {Number} timestamp
     */
    formatDateString(timestamp) {
      return dateI18n('d/m/Y', moment.unix(timestamp));
    }
  }
};
