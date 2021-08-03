import React from 'react';

import { pick } from 'lodash';
import { __ } from '@wordpress/i18n';
import {
  BaseControl,
  TextControl,
  SelectControl,
  TextareaControl
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { withAPI } from '../../api';
import AddressSelect from '../elements/AddressSelect';
import BaseCreateOrder from '../create-shipping/BaseCreateOrder';
import GHNShippingService from './GHNShippingService';
import { validationSchema, requiredNoteOptions } from './schema';
import * as Styles from './styles';

class CreateOrderForm extends BaseCreateOrder {
  constructor(props) {
    super(props);

    this.state = {
      ...this.state,
      services: [],
      servicesErrors: null
    };

    this.fetchAvailableServices = this.fetchAvailableServices.bind(this);
  }

  componentDidMount() {
    this.fetchAvailableServices();
  }

  getValidationSchema() {
    return validationSchema;
  }

  getCreateValues(data) {
    const { address_data: address } = data;

    return {
      ...pick(data, [
        'width',
        'height',
        'weight',
        'length',
        'note',
        'required_note',
        'coupon'
      ]),
      to_name: data.name || '',
      to_phone: data.phone || '',
      to_address: data.address || '',
      to_district_id: address.district || 0,
      to_ward_code: address.ward || '',
      cod_amount: parseInt(data.cod || 0, 10),
      insurance_value: data.insurance || 0,
      payment_type_id: 1,
      service_type_id: 1,
      service_id: parseInt(data.service_id, 10)
    };
  }

  fetchAvailableServices() {
    const { values } = this.formik.current;
    const { getAvailableServices } = this.props;

    if (values?.address_data?.district) {
      this.setState({ services: null });

      getAvailableServices({
        courier: 'ghn',
        data: { to_district: values.address_data.district }
      }).then(services => this.setState({ services }))
        .catch(servicesErrors => this.setState({ servicesErrors }));
    }
  }

  renderForm(formik, getInputProps) {
    const { services } = this.state;

    return (
      <>
        <section>
          <h3>Bên nhận</h3>

          <div className="form-row">
            <div className="form-column">
              <div className="form-control">
                <TextControl
                  name="name"
                  label={__('Họ tên', 'vn-shipping')}
                  {...getInputProps('name')}
                />
              </div>

              <div className="form-control">
                <TextControl
                  name="phone"
                  label={__('Điện thoại', 'vn-shipping')}
                  {...getInputProps('phone')}
                />
              </div>
            </div>

            <div className="form-column">

              <div className="form-group">
                <div className="form-control">
                  <TextControl
                    label={__('Địa Chỉ', 'vn-shipping')}
                    {...getInputProps('address')}
                  />
                </div>
              </div>

              <div className="form-group">
                <div className="form-control">
                  <BaseControl label={'Quận/Huyện'}>

                    <AddressSelect
                      {...getInputProps('address_data')}
                    />
                  </BaseControl>
                </div>
              </div>
            </div>
          </div>

        </section>

        <section>
          <h3>Hàng hoá</h3>

          <div className="form-row">
            <div className="form-column">
              <div className="form-group">
                <div className="form-control">
                  <TextControl
                    type="number"
                    min={0}
                    max={50_000_000}
                    label={__('Thu hộ tiền COD (VNĐ)', 'vn-shipping')}
                    {...getInputProps('cod')}
                  />
                </div>
              </div>

              <div className="form-group">
                <div className="form-control">
                  <TextControl
                    type="number"
                    min={0}
                    max={10_000_000}
                    label={__('Tổng giá trị hàng hoá (VNĐ)', 'vn-shipping')}
                    {...getInputProps('insurance')}
                  />

                  <small className="form-text text-muted">
                    <a href="https://ghn.vn/pages/quy-dinh-ve-khieu-nai-cua-ghn"
                       target="_blank">Qui trình</a>
                    &nbsp; &amp; &nbsp;
                    <a href="https://ghn.vn/pages/chinh-sach-boi-thuong-cua-ghn"
                       target="_blank">Chính sách xử lý đền bù</a>
                  </small>
                </div>
              </div>
            </div>

            <div className="form-column">
              <div className="form-group">
                <div className="form-control">
                  <TextControl
                    type="number"
                    min={0}
                    max={1_600_000}
                    label={__('Khối lượng (gram)', 'vn-shipping')}
                    {...getInputProps('weight')}
                  />
                </div>
              </div>

              <div className="form-group form-group--col3">
                <div className="form-control">
                  <TextControl
                    type="number"
                    label={__('Dài (cm)', 'vn-shipping')}
                    min={0}
                    max={200}
                    {...getInputProps('length')}
                  />
                </div>

                <div className="form-control">
                  <TextControl
                    type="number"
                    min={0}
                    max={200}
                    label={__('Rộng (cm)', 'vn-shipping')}
                    {...getInputProps('width')}
                  />
                </div>

                <div className="form-control">
                  <TextControl
                    type="number"
                    min={0}
                    max={200}
                    label={__('Cao (cm)', 'vn-shipping')}
                    {...getInputProps('height')}
                  />
                </div>
              </div>
            </div>
          </div>

        </section>

        <section>
          <h3>Gói cước</h3>

          {(services && services.length > 0) && (
            <Styles.ServiceList>
              {services.map(service =>
                <GHNShippingService
                  key={service.service_id}
                  data={formik.values}
                  serviceInfo={service}
                  inputProps={{
                    name: 'service_id',
                    onBlur: formik.handleBlur,
                    onChange: formik.handleChange
                  }}
                />
              )}
            </Styles.ServiceList>
          )}
        </section>

        <section>
          <h3>Lưu ý - Ghi chú</h3>

          <div className="form-control">
            <SelectControl
              label={__('Lưu ý giao hàng', 'vn-shipping')}
              required
              options={requiredNoteOptions}
              {...getInputProps('required_note')}
            />
          </div>

          <div className="form-control">
            <TextareaControl
              label={__('Ghi chú', 'vn-shipping')}
              {...getInputProps('note')}
            />
          </div>
        </section>
      </>
    );
  }
}

export default withAPI((actions) => {
  return pick(actions, [
    'createShippingOrder',
    'getAvailableServices'
  ]);
})(CreateOrderForm);
