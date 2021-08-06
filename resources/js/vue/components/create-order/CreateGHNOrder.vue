<template>
  <form ref="form" class="vns-create-form" @submit.prevent="submit">
    <div class="vns-create-form__main">
      <section>
        <h3>Bên nhận</h3>

        <div class="vns-form-group">
          <div class="vns-form-control">
            <label for="shipping_name">Họ tên</label>
            <input type="text" name="name" id="shipping_name" v-model="name">
          </div>

          <div class="vns-form-control">
            <label for="shipping_phone">Điện thoại</label>
            <input type="text" name="name" id="shipping_phone" v-model="phone">
          </div>
        </div>

        <div class="form-group">
          <div class="vns-form-control">
            <label for="shipping_address">Địa Chỉ</label>
            <input type="text" name="name" id="shipping_address" v-model="address">
          </div>
        </div>

        <div class="vns-form-control">
          <label>Quận/Huyện</label>
          <address-field v-model="address_data" />
        </div>
      </section>

      <section>
        <h3>Hàng hoá</h3>

        <div class="vns-form-group is-3-columns">
          <div class="vns-form-control">
            <label for="length">Dài (cm)</label>

            <input
              v-model.number="length"
              type="number"
              id="length"
              name="length"
              min="0"
              max="200"
              required
            />
          </div>

          <div class="vns-form-control">
            <label for="width">Rộng (cm)</label>

            <input
              v-model.number="width"
              type="number"
              id="width"
              name="width"
              min="0"
              max="200"
              required
            />
          </div>

          <div class="vns-form-control">
            <label for="height">Cao (cm)</label>

            <input
              v-model.number="height"
              type="number"
              id="height"
              name="height"
              min="0"
              max="200"
              required
            />
          </div>
        </div>

        <div class="vns-form-group is-3-columns">
          <div class="vns-form-control">
            <label for="weight">Khối lượng (gram)</label>

            <input
              v-model.number="weight"
              type="number"
              id="weight"
              name="weight"
              min="0"
              max="1600000"
            />
          </div>

          <div class="vns-form-control">
            <label for="insurance">Tổng giá trị hàng hoá (VNĐ)</label>

            <input
              v-model.number="insurance"
              type="number"
              id="insurance"
              name="insurance"
              min="0"
              max="10000000"
            />

            <small class="form-text text-muted">
              <a href="https://ghn.vn/pages/quy-dinh-ve-khieu-nai-cua-ghn"
                 target="_blank">Qui trình</a>
              &nbsp; &amp; &nbsp;
              <a href="https://ghn.vn/pages/chinh-sach-boi-thuong-cua-ghn"
                 target="_blank">Chính sách xử lý đền bù</a>
            </small>
          </div>
        </div>
      </section>

      <section>
        <h3>Lưu ý - Ghi chú</h3>

        <div class="vns-form-control">
          <label for="required_note">Lưu ý giao hàng</label>

          <select name="required_note" id="required_note" v-model="required_note" required>
            <option value="KHONGCHOXEMHANG">Không cho xem hàng</option>
            <option value="CHOXEMHANGKHONGTHU">Cho xem hàng</option>
            <option value="CHOTHUHANG">Cho thử hàng</option>
          </select>
        </div>

        <div class="vns-form-control">
          <label for="note">Ghi chú</label>
          <textarea name="note" id="note" rows="4" v-model="note"></textarea>
        </div>
      </section>
    </div>

    <div class="vns-create-form__side">
      <div class="vns-create-form__submit">
        <h3>Gói cước</h3>

        <block-ui
          :is-loading="isLoading('getAvailableServices')"
          :is-small="true">
          <ul class="vns-service-list" v-if="availableServices">
            <li v-for="service in availableServices" :key="service.service_id">
              <ghn-service-item
                :service="service"
                :is-checked="service.service_id === service_id"
                :fee-info="serviceFees[service.service_id] || null"
                :lead-time-info="serviceLeadTimes[service.service_id] || null"
                @service-selected="setServiceId"
              />
            </li>
          </ul>
        </block-ui>

        <div class="vns-form-control">
          <label>Người thanh toán</label>
          <label style="display: inline-block; margin-right: 1.5rem;">
            <input
              v-model.number="payment_type_id"
              type="radio"
              name="payment_type_id"
              value="1"
            />
            <span>Người gửi</span>
          </label>

          <label style="display: inline-block;">
            <input
              v-model.number="payment_type_id"
              type="radio"
              name="payment_type_id"
              value="2"
            />
            <span>Người nhận</span>
          </label>
        </div>

        <div class="vns-form-control">
          <label for="cod">Thu hộ tiền COD (VNĐ)</label>

          <input
            v-model.number="cod"
            type="number"
            id="cod"
            name="cod"
            min="0"
            max="50000000"
          />
        </div>

        <table class="table">
          <tbody>
            <tr>
              <th></th>
              <td></td>
            </tr>

            <tr>
              <th>Tổng phí</th>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>

      <button type="submit" class="button button-primary">
        Tạo mã vận đơn
      </button>
    </div>
  </form>
</template>

<script>
import { castArray, debounce } from 'lodash';

import BlockUi from '../../elements/BlockUi';
import AddressField from '../../elements/AddressField';
import GhnServiceItem from './GHNServiceItem';

import { InteractsWithAPI, InteractsWithCreateOrder } from '../../api';

export default {
  name: 'CreateGHNOrder',

  components: {
    BlockUi,
    AddressField,
    GhnServiceItem
  },

  mixins: [
    InteractsWithAPI,
    InteractsWithCreateOrder
  ],

  data() {
    return {
      service_id: null,
      service_type_id: null,
      payment_type_id: 1,
      required_note: 'KHONGCHOXEMHANG',

      serviceFees: {},
      serviceLeadTimes: {},
      availableServices: []
    };
  },

  created() {
    this.debounceFetchFees = debounce(this.fetchFees, 450);
    this.debounceFetchServices = debounce(this.fetchServices, 550);

    this.fetchServices();

    this.$watch(
      () => this.address_data?.district,
      this.debounceFetchServices
    );

    this.$watch('width', this.debounceFetchFees);
    this.$watch('height', this.debounceFetchFees);
    this.$watch('length', this.debounceFetchFees);
    this.$watch('weight', this.debounceFetchFees);

    this.$watch('availableServices', (newServices) => {
      if ((newServices && newServices.length > 0) && !this.service_id) {
        this.setServiceId(newServices[0]);
      }
    });
  },

  unmounted() {
    if (this.debounceFetchFees) {
      this.debounceFetchFees.cancel();
    }

    if (this.debounceFetchServices) {
      this.debounceFetchServices.cancel();
    }
  },

  methods: {
    setServiceId(serviceInfo) {
      this.service_id = serviceInfo.service_id;
      this.service_type_id = serviceInfo.service_type_id;
    },

    fetchFees() {
      if (!this.availableServices || this.availableServices.length === 0) {
        return;
      }

      if (!this.width || !this.height || !this.weight || !this.length) {
        return;
      }

      for (const service of this.availableServices) {
        this.getShippingFee('ghn', {
          width: this.width,
          height: this.height,
          length: this.length,
          weight: this.weight,
          service_id: service.service_id,
          service_type_id: service.service_type_id,
          to_district_id: this.address_data.district,
          to_ward_code: this.address_data.ward
        }).then(response => {
          this.serviceFees[service.service_id] = response;
        });

        this.getLeadTime('ghn', {
          service_id: service.service_id,
          service_type_id: service.service_type_id,
          to_district_id: this.address_data.district,
          to_ward_code: this.address_data.ward
        }).then(response => {
          this.serviceLeadTimes[service.service_id] = response;
        });
      }
    },

    async fetchServices() {
      if (!this.address_data?.district) {
        return;
      }

      this.availableServices = null;

      const response = await this.getAvailableServices('ghn', {
        to_district: this.address_data.district
      });

      this.availableServices = castArray(response);
      if (this.availableServices) {
        this.fetchFees();
      }
    }
  },

  computed: {
    shippingFee() {
      // return this.
    }
  }
};
</script>
