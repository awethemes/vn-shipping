<template>
  <div class="vns-tracking-info">
    <p class="vns-tracking-info__logo">
      <img :src="courier.icon" :alt="courier.name">
      <span class="screen-reader-text">{{ shippingData.courier_name }}</span>
    </p>

    <p>Mã giao vận</p>
    <p class="vns-tracking-info__code">
      <span>{{ shippingData.tracking_number }}</span>
    </p>
  </div>
</template>

<script>
import { store } from '../store';

export default {
  name: 'ShippingInfo',

  props: ['shippingData'],

  computed: {
    courier() {
      return store.getCourierInfo(
        this.shippingData.courier
      );
    }
  }
};
</script>
