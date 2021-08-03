import React from 'react';

import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { COURIERS } from '../../utils/couriers';
import CourierItem from './CourierItem';
import * as Style from './styles';

function ChooseCourier({
  courier,
  setCourier,
  onPressSelect,
  isOpen = false,
  onRequestClose = () => {}
}) {
  if (!isOpen) {
    return null;
  }

  const availableItems = COURIERS.map((data) =>
    <CourierItem
      key={data.id}
      courier={data}
      onChange={setCourier}
      isChecked={courier?.id === data.id}
    />
  );

  return (
    <Modal
      title={__('Chọn đơn vị vận chuyển', 'vn-shipping')}
      onRequestClose={onRequestClose}
      isDismissible={true}
      shouldCloseOnEsc={true}
      shouldCloseOnClickOutside={false}
    >
      <h3>Tạo mới</h3>

      <Style.CourierList>
        {availableItems}
      </Style.CourierList>

      <Style.Actions>
        <Button
          isPrimary
          disabled={!courier}
          onClick={onPressSelect}
        >
          {__('Tiếp theo', 'vn-shipping')}
        </Button>
      </Style.Actions>
    </Modal>
  );
}

export default ChooseCourier;
