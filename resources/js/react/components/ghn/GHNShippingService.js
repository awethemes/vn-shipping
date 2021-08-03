import React, { useEffect, useState } from 'react';
import * as PropTypes from 'prop-types';
import { omit, pick } from 'lodash';

/**
 * Internal dependencies
 */
import { useAPI } from '../../api';
import { addNotice } from '../../utils/notices';
import { formatCurrency, formatDateString } from '../../utils/formating';

function GHNShippingService({ serviceInfo, data, inputProps }) {
  const [feeInfo, setFeeInfo] = useState(null);
  const [leadTime, setLeadTime] = useState(null);

  const {
    getLeadTime,
    getShippingFee
  } = useAPI();

  useEffect(() => {
    const _data = {
      ...pick(data, ['weight', 'width', 'height', 'length']),
      service_id: serviceInfo.service_id,
      service_type_id: serviceInfo.service_type_id,
      to_district_id: data.address_data.district,
      to_ward_code: data.address_data.ward
    };

    setFeeInfo(null);
    setLeadTime(null);

    getShippingFee({
      courier: 'ghn',
      data: _data
    }).then(response => {
      setFeeInfo(response);
    }).catch(err => {
      if (err.message) {
        addNotice(err.message);
      }
    });

    getLeadTime({
      courier: 'ghn',
      data: omit(_data, ['weight', 'width', 'height', 'length'])
    }).then(response => {
      setLeadTime(response);
    }).catch(err => {
      console.log(err);
    });
  }, [serviceInfo, data.weight, data.width, data.height, data.length]);

  return (
    <li>
      <input
        {...inputProps}
        type="radio"
        value={serviceInfo.service_id}
        id={`service_id_${serviceInfo.service_id}`}
      />

      <label htmlFor={`service_id_${serviceInfo.service_id}`}>
        <p><strong>{serviceInfo.short_name}</strong></p>

        <p>
          {feeInfo ? formatCurrency(feeInfo.total) : null}
        </p>

        {(leadTime && leadTime.leadtime)
          ? (<p>Ngày giao dự kiến: {formatDateString(leadTime.leadtime)} </p>)
          : null
        }
      </label>
    </li>
  );
}

GHNShippingService.propTypes = {
  serviceInfo: PropTypes.object.isRequired
};

export default GHNShippingService;
