import React from 'react';
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { getCourier } from '../utils/couriers';

const TrackingCode = styled.div`
  border: dashed 2px #737373;
  display: flex;
  padding: 1rem;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;

  p {
    margin: 0;
  }

  strong {
    color: #737373;
  }

  span {
    color: #ff9800;
    font-weight: 700;
    font-size: 1.6rem;
  }
`;

function ShipmentInfo({ shipmentInfo }) {
  const { courier, tracking_number } = shipmentInfo;
  const courierInfo = getCourier(courier);

  return (
    <>
      <TrackingCode>
        <p><strong>{courierInfo.name}</strong></p>
        <p><span>{tracking_number}</span></p>
      </TrackingCode>
    </>
  );
}

ShipmentInfo.propTypes = {
  shipmentInfo: PropTypes.object.isRequired
};

export default ShipmentInfo;
