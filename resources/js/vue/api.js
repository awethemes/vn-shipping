import moment from 'moment';
import apiFetch from '@wordpress/api-fetch';
import { dateI18n } from '@wordpress/date';
import { addQueryArgs } from '@wordpress/url';

import { store, currency } from './store';

const safeApiFetch = async (...args) => {
  try {
    return await apiFetch(...args);
  } catch (error) {
    console.error(error);

    let message = error instanceof Error || error.message
      ? error.message
      : null;

    if (message) {
    }

    throw error;
  }
};

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

      return safeApiFetch({ path }).finally(() => {
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
      data = { order_id: this.currentOrderId, ...data };

      this.isRequesting['getAvailableServices'] = true;

      return safeApiFetch({ method: 'POST', path, data }).finally(() => {
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

      return safeApiFetch({ method: 'POST', path, data }).finally(() => {
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

      return safeApiFetch({ method: 'POST', path, data });
    },

    /**
     * @param courier
     * @param data
     * @returns {Promise}
     */
    getLeadTime(courier, data) {
      const path = `/awethemes/vn-shipping/shipping/${courier}/lead-time`;

      data = { order_id: this.currentOrderId, ...data };

      return safeApiFetch({ method: 'POST', path, data });
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

  emits: ['order-created'],

  data() {
    const shipping = this.orderShippingInfo.shipping || {};

    return {
      name: shipping.name || null,
      phone: shipping.phone || null,
      address: shipping.address || null,
      address_data: shipping.address_data || {},
      cod: shipping.cod || 0,
      insurance: shipping.insurance || 0,
      width: shipping.width || 0,
      height: shipping.height || 0,
      length: shipping.length || 0,
      weight: shipping.weight || 0,
      note: shipping.note || null
    };
  },

  methods: {
    async submit() {
      const validated = await this.validate();
      if (validated === false) {
        return;
      }

      try {
        const response = await this.createShippingOrder('ghn', this.ghnCreationData);

        if (response.tracking_number) {
          this.$emit('order-created', response.tracking_number, response);
        }
      } catch (error) {
        if (error.message) {
          alert(error.message);
        }
      }
    },

    validate() {
    }
  },

  computed: {
    ghnCreationData() {
      return {
        width: this.width,
        height: this.height,
        weight: this.weight,
        length: this.length,
        note: this.note,
        required_note: this.required_note,
        coupon: this.coupon,
        to_name: this.name || '',
        to_phone: this.phone || '',
        to_address: this.address || '',
        to_district_id: this.address_data?.district || 0,
        to_ward_code: this.address_data?.ward || '',
        cod_amount: parseInt(this.cod || 0, 10),
        insurance_value: this.insurance || 0,
        service_type_id: parseInt(this.service_type_id, 10),
        service_id: parseInt(this.service_id, 10),
        payment_type_id: parseInt(this.payment_type_id, 10)
      };
    }
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
