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
              max="50000000"
            />
          </div>
        </div>
      </section>

      <section>
        <h3>Thông tin lấy hàng</h3>

        <div class="vns-form-control">
          <label>Hình thức gửi hàng</label>

          <label style="display: inline-block;">
            <input
              v-model="pick_option"
              type="radio"
              value="cod"
              name="pick_option"
            />
            <span>Lấy hàng tại shop</span>
          </label>

          <label style="display: inline-block; margin-left: 1.5rem;">
            <input
              v-model="pick_option"
              type="radio"
              value="post"
              name="pick_option"
            />
            <span>Shop gửi hàng tại bưu cục</span>
          </label>
        </div>
      </section>

      <section>
        <h3>Ghi chú</h3>
        <div class="vns-form-control">
          <label for="note">Ghi chú (không quá 120 ký tự)</label>
          <textarea name="note" id="note" rows="4" v-model="note" maxlength="120"></textarea>
        </div>
      </section>
    </div>

    <div class="vns-create-form__side">
      <div class="vns-create-form__submit">
        <h3>Gói cước</h3>

        <div class="vns-form-control">
          <label>Hình thức vận chuyển</label>

          <label style="display: inline-block; margin-right: 1.5rem;">
            <input
              v-model.number="transport"
              type="radio"
              value="fly"
              name="transport"
            />
            <span>Đường bay</span>
          </label>

          <label style="display: inline-block;">
            <input
              v-model.number="transport"
              type="radio"
              value="road"
              name="transport"
            />
            <span>Đường bộ</span>
          </label>

          <p style="margin: 0; color: #999;">
            <i>Nếu phương thức vận chuyển không hợp lệ thì GHTK sẽ tự động nhảy về PTVC mặc định</i>
          </p>
        </div>

        <div class="vns-form-control">
          <label>Phí ship</label>

          <label style="display: inline-block; margin-right: 1.5rem;">
            <input
              v-model.number="is_freeship"
              :value="1"
              type="radio"
              name="is_freeship"
            />
            <span>Shop trả</span>
          </label>

          <label style="display: inline-block;">
            <input
              v-model.number="is_freeship"
              :value="0"
              type="radio"
              name="is_freeship"
            />
            <span>Khách trả</span>
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

        <button type="submit" class="button button-primary" :disabled="!isValid">
          Tạo mã vận đơn
        </button>
      </div>

    </div>
  </form>
</template>

<script>
import Notice from '../../elements/Notice';
import BlockUi from '../../elements/BlockUi';
import AddressField from '../../elements/AddressField';

import {
  FormattingMixin,
  InteractsWithAPI,
  InteractsWithCreateOrder
} from '../../api';

const TAGS = {
  '1': 'Dễ vỡ',
  '7': 'Nông sản/thực phẩm khô'
};

export default {
  name: 'CreateGHTKOrder',

  components: {
    Notice,
    BlockUi,
    AddressField
  },

  mixins: [
    FormattingMixin,
    InteractsWithAPI,
    InteractsWithCreateOrder
  ],

  data() {
    return {
      pick_option: 'cod', // cod | post
      transport: 'road',
      is_freeship: 0,
      tags: [], //

      errors: {}
    };
  },

  computed: {
    isValid() {
      return true;
    }
  }
};
</script>
