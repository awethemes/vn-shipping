<template>
  <div class="vns-form-group is-3-columns">
    <div>
      <woo-select v-model="province" :options="provinceOptions" />
    </div>

    <div>
      <woo-select v-model="district" :options="districtOptions" />
    </div>

    <div>
      <woo-select v-model="ward" :options="wardOptions" />
    </div>
  </div>
</template>

<script>
import WooSelect from './WooSelect';
import { getProvince, getDistrict, getWards } from '../../utils/request';

export default {
  name: 'AddressField',

  components: { WooSelect },

  props: ['modelValue'],

  data() {
    const address = {
      province: this.modelValue.province || null,
      district: this.modelValue.district || null,
      ward: this.modelValue.ward || null
    };

    return {
      ...address,
      provinceOptions: address.province ? [{ value: address.province, label: '' }] : [],
      districtOptions: address.province ? [{ value: address.province, district: '' }] : [],
      wardOptions: address.province ? [{ value: address.province, ward: '' }] : [],
    };
  },

  created() {
    this.loadProvinces();

    this.$watch('province', (newCode, oldCode) => {
      console.log(newCode, oldCode);

      this.ward = null;
      this.district = null;

      this.emitChange();
      this.loadDistrict();
    });

    this.$watch('district', (newCode, oldCode) => {
      console.log(newCode, oldCode);

      this.ward = null;

      this.emitChange();
      this.loadWards();
    });

    this.$watch('ward', () => {
      this.emitChange();
    });
  },

  methods: {
    emitChange() {
      this.$emit('update:modelValue', this.data);
    },

    async loadProvinces() {
      this.provinceOptions = await getProvince();
    },

    async loadWards() {
      if (this.district && this.province) {
        this.wardOptions = await getWards(this.district, this.province);
      }
    },

    async loadDistrict() {
      if (this.province) {
        this.districtOptions = await getDistrict(this.province);
      }
    }
  },

  computed: {
    data() {
      return {
        province: this.province,
        district: this.district,
        ward: this.ward
      };
    }
  }
};
</script>

<style scoped lang="scss">
.vns-address-control {
  display: flex;
}
</style>
