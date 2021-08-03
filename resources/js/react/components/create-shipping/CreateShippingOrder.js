import React from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import { withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { withAPI } from '../../api';
import Modal from '../elements/Modal';
import RequestError from '../elements/RequestError';
import CreateOrderForm from '../ghn/CreateOrderForm';
import ChooseCourier from './ChooseCourier';
import { SwalAlert } from '../../utils/sweetalert';
import { STORE_NAME } from '../../store/constants';

class CreateShippingOrder extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      errors: null,
      isLoading: false,

      isSelectModalOpen: false,
      isCreateModalOpen: false,

      courier: null,
      shippingData: null
    };

    this.onChooseCourier = this.onChooseCourier.bind(this);
    this.onCreateSuccess = this.onCreateSuccess.bind(this);
  }

  onChooseCourier() {
    const { getOrderShipping } = this.props;

    this.setState({
      shippingData: null,
      isSelectModalOpen: false,
      isCreateModalOpen: true
    });

    this.setState({ isLoading: true });

    getOrderShipping()
      .catch(errors => this.setState({ errors }))
      .then(shippingData => this.setState({ shippingData }))
      .finally(() => this.setState({ isLoading: false }));
  }

  onCreateSuccess(response) {
    const { setShipmentInfo } = this.props;
    const trackingNumber = response.tracking_number;

    setShipmentInfo(response);
    this.setState({ isCreateModalOpen: false });

    SwalAlert(`Tạo mã vận đơn thàng thành công. Mã vận đơn: ${trackingNumber}`);
  }

  render() {
    const {
      courier,
      shippingData,
      errors,
      isLoading,
      isSelectModalOpen,
      isCreateModalOpen
    } = this.state;

    const setCourier = (courier) => this.setState({ courier });
    const setIsSelectModalOpen = (state) => this.setState({ isSelectModalOpen: state });
    const setIsCreateModalOpen = (state) => this.setState({ isCreateModalOpen: state });

    return (
      <>
        <Button
          isPrimary
          onClick={() => setIsSelectModalOpen(true)}
        >
          {__('Tạo mã đơn vận chuyển', 'vn-shipping')}
        </Button>

        <ChooseCourier
          courier={courier}
          setCourier={setCourier}
          onPressSelect={this.onChooseCourier}
          onRequestClose={() => setIsSelectModalOpen(false)}
          isOpen={isSelectModalOpen}
        />

        {courier && (
          <Modal
            isOpen={isCreateModalOpen}
            isLoading={isLoading}
            title={sprintf(__('Tạo đơn: %s', 'vn-shipping'), courier.title)}
            onRequestClose={() => setIsCreateModalOpen(false)}
          >
            {errors ? <RequestError errors={errors} /> : null}

            {shippingData?.shipping && (
              <CreateOrderForm
                shippingInfo={shippingData.shipping}
                onCreateSuccess={this.onCreateSuccess}
              />
            )}
          </Modal>
        )}
      </>
    );
  }
}

export default compose(
  withDispatch((dispatch) => {
    const { setShipmentInfo } = dispatch(STORE_NAME);

    return {
      setShipmentInfo
    };
  }),

  withAPI((actions) => {
    const {
      getOrderShipping,
      createShippingOrder
    } = actions;

    return {
      getOrderShipping,
      createShippingOrder
    };
  })
)(CreateShippingOrder);
