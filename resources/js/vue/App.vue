<template>
  <template v-if="states.orderShippingData">
    <shipping-info :shipping-data="states.orderShippingData" />

    <div class="vns-actions">
      <a href="#" class="is-destroy" @click.prevent="deleteShippingOrder">Hủy</a>
      <!--      <button class="button btn-check">Chi tiết</button>-->
    </div>

    <dialog-message ref="deleteDialog" :is-confirm="true"></dialog-message>
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
          v-if="createOrderComponent"
          :is="createOrderComponent"
          :order-shipping-info="states.orderShippingInfo"
          @order-created="onOrderCreated"
          @create-order-error="onOrderCreateError"
        />

        <div v-else>
          <p>Tạo đơn hàng cho nhà vận chuyển chưa được hỗ trợ</p>
        </div>
      </template>
    </modal>
  </template>

  <dialog-message
    ref="createOrderSuccess"
    title="Tạo đơn hàng thành công!"
    :is-confirm="false"
  />

  <dialog-message
    ref="createOrderError"
    title="Lỗi tạo đơn hàng"
    :is-confirm="false"
  />
</template>

<script>
import { store } from './store';
import { InteractsWithAPI } from './api';

import Modal from './elements/Modal';
import Loading from './elements/Loading';
import DialogMessage from './elements/DialogMessage';

import ShippingInfo from './components/ShippingInfo';
import ChooseCourier from './components/ChooseCourier';
import CreateVTPOrder from './components/create/CreateVTPOrder';
import CreateGHNOrder from './components/create/CreateGHNOrder';
import CreateGHTKOrder from './components/create/CreateGHTKOrder';

export default {
  name: 'App',

  mixins: [InteractsWithAPI],

  components: {
    Modal,
    Loading,
    DialogMessage,

    ShippingInfo,
    ChooseCourier,

    CreateVTPOrder,
    CreateGHNOrder,
    CreateGHTKOrder
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

        case 'ghtk':
        case 'giao_hang_tiet_kiem':
          return 'CreateGHTKOrder';

        case 'vtp':
        case 'viettel_post':
          return 'CreateVTPOrder';

        default:
          return null;
      }
    }
  },

  methods: {
    onOrderCreateError(message) {
      if (message) {
        this.$refs.createOrderError.open(message);
      }
    },

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
    },

    async deleteShippingOrder() {
      const isConfirmed = await this.$refs.deleteDialog.open(
        'Bạn có chắc muốn xóa mã vận đơn này?'
      );

      if (!isConfirmed) {
        return;
      }

      const response = await this.cancelShippingOrder();

      if (response) {
        store.setShippingData(null);
        store.setOrderShippingInfo(null);
      }
    }
  }
};
</script>
