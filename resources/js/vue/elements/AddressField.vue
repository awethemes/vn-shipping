<template>
  <div class="vns-form-group is-3-columns">
    <div>
      <woo-select
        v-model="province"
        :required="true"
        :options="provinceOptions"
        placeholder="Tỉnh/Thành Phố"
      />
    </div>

    <div>
      <woo-select
        v-model="district"
        :required="true"
        :disabled="isLoading.district"
        :options="districtOptions"
        placeholder="Quận/Huyện"
      />
    </div>

    <div>
      <woo-select
        v-model="ward"
        :required="true"
        :disabled="isLoading.ward"
        :options="wardOptions"
        placeholder="Xã/Phường"
      />
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
      isLoading: { district: false, ward: false },
      provinceOptions: address.province ? [{ value: address.province, label: '' }] : [],
      districtOptions: address.province ? [{ value: address.province, district: '' }] : [],
      wardOptions: address.province ? [{ value: address.province, ward: '' }] : []
    };
  },

  created() {
    this.loadInitData();
  },

  watch: {
    province() {
      this.district = null;
      this.ward = null;

      this.districtOptions = [];
      this.wardOptions = [];

      this.emitChange();
      this.loadDistrict();
    },

    district() {
      this.ward = null;
      this.wardOptions = [];

      this.emitChange();
      this.loadWards();
    },

    ward() {
      this.emitChange();
    }
  },

  methods: {
    emitChange() {
      this.$emit('update:modelValue', this.data);
    },

    async loadInitData() {
      await this.loadProvinces();
      await this.loadDistrict();
      await this.loadWards();
    },

    async loadProvinces() {
      this.provinceOptions = await getProvince();
    },

    async loadDistrict() {
      if (this.province) {
        this.isLoading.district = true;

        this.districtOptions = await getDistrict(this.province);

        this.isLoading.district = false;
      }
    },

    async loadWards() {
      if (this.district && this.province) {
        this.isLoading.ward = true;

        this.wardOptions = await getWards(this.district, this.province);

        this.isLoading.ward = false;
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
