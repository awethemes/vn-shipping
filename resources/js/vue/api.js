import moment from 'moment';
import apiFetch from '@wordpress/api-fetch';
import { dateI18n } from '@wordpress/date';
import { addQueryArgs } from '@wordpress/url';

import { store, currency } from './store';

const safeApiFetch = async (...args) => {
  try {
    return await apiFetch(...args);
  } catch (error) {
    const code = error.status || 0;

    if (code >= 500) {
      throw new Error('Lỗi hệ thống, vui lòng thử lại!');
    }

    if (error.message) {
      throw new Error(error.message);
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
     * @param {Object} data
     * @returns {Promise}
     */
    cancelShippingOrder(data = {}) {
      const path = `/awethemes/vn-shipping/shipping/${this.currentOrderId}/cancel`;

      this.isRequesting['cancelShippingOrder'] = true;

      return safeApiFetch({ method: 'POST', path, data }).finally(() => {
        this.isRequesting['cancelShippingOrder'] = false;
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

  emits: [
    'order-created',
    'create-order-error'
  ],

  data() {
    const shipping = this.orderShippingInfo.shipping || {};

    return {
      name: shipping.name || '',
      phone: shipping.phone || '',
      address: shipping.address || '',
      address_data: shipping.address_data || {},
      cod: shipping.cod || 0,
      insurance: shipping.insurance || 0,
      width: shipping.width || 0,
      height: shipping.height || 0,
      length: shipping.length || 0,
      weight: shipping.weight || 0,
      note: shipping.note || ''
    };
  },

  methods: {
    async submit() {
      const validated = await this.validate();
      if (validated === false) {
        return;
      }

      let data = {};
      switch (store.states.selectedCourier) {
        case 'giao_hang_nhanh':
          data = this.ghnCreationData;
          break;
        case 'giao_hang_tiet_kiem':
          data = this.ghtkCreationData;
          break;
      }

      try {
        const response = await this.createShippingOrder(
          store.states.selectedCourier,
          data
        );

        if (response.tracking_number) {
          this.$emit('order-created', response.tracking_number, response);
        }
      } catch (error) {
        console.warn(error);

        if (error.message) {
          this.$emit('create-order-error', error.message);
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
    },

    ghtkCreationData() {
      return {
        order: {
          id: String(store.states.orderId),
          name: this.name || '',
          email: this.email || '',
          tel: this.phone || '',
          address: this.address || '',
          province: this.address_data?.province || 0,
          district: this.address_data?.district || 0,
          ward: this.address_data?.ward || 0,
          note: this.note,

          value: this.insurance,
          pick_money: this.cod,
          total_weight: this.weight,

          transport: this.transport,
          pick_option: this.pick_option,
          is_freeship: this.is_freeship,
          tags: this.tags
        }
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
