<template>
  <div>
    <woo-select v-model="province" :options="provinceOptions" />
    <woo-select v-model="district" :options="districtOptions" />
    <woo-select v-model="ward" :options="wardOptions" />
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
    return {
      provinceOptions: [],
      districtOptions: [],
      wardOptions: [],

      province: this.modelValue.province || null,
      district: this.modelValue.district || null,
      ward: this.modelValue.ward || null
    };
  },

  created() {
    this.loadProvinces();

    this.$watch('province', () => {
      this.ward = null;
      this.district = null;

      this.emitChange();
      this.loadDistrict();
    });

    this.$watch('district', () => {
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
