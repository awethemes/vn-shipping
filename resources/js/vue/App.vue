<template>
  <template v-if="states.orderShippingData">
    <shipping-info :shipping-data="states.orderShippingData" />
  </template>

  <template v-else>
    <button
      class="button button-primary"
      @click.prevent="isSelectCourierModalOpen = true">
      Tạo mã vận đơn
    </button>

    <modal
      v-if="isSelectCourierModalOpen"
      title="Chọn đơn vị vận chuyển"
      @modal-close="isSelectCourierModalOpen = false">
      <choose-courier
        :current-courier="states.selectedCourier"
        :available-couriers="states.availableCouriers"
        :order-shipping-methods="states.orderShippingMethods"
        @press-next="onChooseCourier"
      />
    </modal>

    <modal
      v-if="isCreateOrderModalOpen"
      title="Create"
      @modal-close="isCreateOrderModalOpen = false">
      <loading v-if="isLoading('getOrderShippingInfo')" />

      <template v-else-if="states.orderShippingInfo">
        <component
          :is="createOrderComponent"
          :order-shipping-info="states.orderShippingInfo"
          @order-created="onOrderCreated"
        />
      </template>
    </modal>
  </template>

  <dialog-message
    ref="createOrderSuccess"
    title="Tạo đơn hàng thành công!"
    :is-confirm="false"
  />
</template>

<script>
import { store } from './store';
import { InteractsWithAPI } from './api';

import Modal from './elements/Modal';
import Loading from './elements/Loading';
import ShippingInfo from './components/shipping-info/ShippingInfo';
import ChooseCourier from './components/create-order/ChooseCourier';
import CreateGHNOrder from './components/create-order/CreateGHNOrder';
import DialogMessage from './elements/DialogMessage';

export default {
  name: 'App',

  mixins: [InteractsWithAPI],

  components: {
    DialogMessage,
    Modal,
    Loading,
    ShippingInfo,
    ChooseCourier,
    CreateGHNOrder
  },

  data() {
    return {
      states: store.states,
      isCreateOrderModalOpen: false,
      isSelectCourierModalOpen: false
    };
  },

  computed: {
    createOrderComponent() {
      switch (this.states?.selectedCourier) {
        case 'ghn':
        case 'giao_hang_nhanh':
          return 'CreateGHNOrder';
      }
    }
  },

  methods: {
    onOrderCreated(trackingNumber, orderData) {
      this.isCreateOrderModalOpen = false;
      store.setShippingData(orderData);

      this.$refs.createOrderSuccess.open(
        `Tạo đơn hàng thành công. Mã vận đơn: ${trackingNumber}.`
      );
    },

    async onChooseCourier(setSelectedCourier) {
      store.setSelectedCourier(setSelectedCourier);
      store.setOrderShippingInfo(null);

      this.isCreateOrderModalOpen = true;
      this.isSelectCourierModalOpen = false;

      const response = await this.getOrderShippingInfo();
      store.setOrderShippingInfo(response);
    }
  }
};
</script>
