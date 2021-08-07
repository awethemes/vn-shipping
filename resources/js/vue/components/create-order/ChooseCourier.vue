<template>
  <ul class="vns-couriers">
    <li
      v-for="courier in availableCouriers"
      :key="courier.id">
      <input
        type="radio"
        name="courier"
        v-model="courierValue"
        :value="courier.id"
        :id="`courier_${courier.id}`"
      />

      <label :for="`courier_${courier.id}`">
        <img :src="courier.icon" :alt="courier.name" />
        <strong>{{ courier.name }}</strong>
      </label>
    </li>
  </ul>

  <div class="vns-actions is-center">
    <button
      class="button button-primary"
      :disabled="!courierValue"
      @click.prevent="$emit('press-next', courierValue)">
      Tiếp theo
    </button>
  </div>
</template>

<script>
import BlockUi from '../../elements/BlockUi';

export default {
  name: 'ChooseCourier',

  components: { BlockUi },

  emit: ['press-next'],

  props: [
    'currentCourier',
    'availableCouriers',
    'orderShippingMethods'
  ],

  data() {
    return {
      courierValue: this.currentCourier
        || this.orderShippingMethods[0]?.id
        || this.availableCouriers[0]?.id
        || ''
    };
  }
};
</script>
