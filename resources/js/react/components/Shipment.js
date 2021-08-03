import React from 'react';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from '../store/constants';
import ShipmentInfo from './ShipmentInfo';
import CreateShippingOrder from './create-shipping/CreateShippingOrder';

function Shipment({ shipmentInfo }) {
  return (
    <>
      {null !== shipmentInfo
        ? <ShipmentInfo shipmentInfo={shipmentInfo} />
        : <CreateShippingOrder />
      }
    </>
  );
}

export default withSelect((select) => {
  return {
    shipmentInfo: select(STORE_NAME).getShipmentInfo()
  };
})(Shipment);
