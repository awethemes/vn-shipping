import React, { useCallback } from 'react';
import PropTypes from 'prop-types';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import Context from './context';
import { useConfig } from '../config';

function Provider({ children }) {
  const { postId } = useConfig();

  const getOrderShipping = useCallback(
    () => {
      const path = addQueryArgs('awethemes/vn-shipping/shipping/show', {
        order_id: postId
      });

      return apiFetch({ path });
    },
    [postId]
  );

  const getAvailableServices = useCallback(
    ({ courier, data }) => {
      const path = `/awethemes/vn-shipping/shipping/${courier}/available-services`;

      data = {
        order_id: postId,
        ...data
      };

      return apiFetch({
        method: 'POST',
        path,
        data
      });
    },
    [postId]
  );

  const getShippingFee = useCallback(
    ({ courier, data }) => {
      const path = `/awethemes/vn-shipping/shipping/${courier}/fee`;

      data = {
        order_id: postId,
        ...data
      };

      return apiFetch({
        method: 'POST',
        path,
        data
      });
    },
    [postId]
  );

  const getLeadTime = useCallback(
    ({ courier, data }) => {
      const path = `/awethemes/vn-shipping/shipping/${courier}/lead-time`;

      data = { order_id: postId, ...data };

      return apiFetch({
        method: 'POST',
        path,
        data
      });
    },
    [postId]
  );

  const createShippingOrder = useCallback(
    (courier, data) => {
      const path = `/awethemes/vn-shipping/shipping/${courier}/create`;

      data = { order_id: postId, ...data };

      return apiFetch({
        method: 'POST',
        path,
        data
      })
        .catch(error => {
          // if (error.response.status != 422) throw error;
          // setErrors(Object.values(error.response.data.errors));
        });
    },
    [postId]
  );

  const state = {
    getOrderShipping,
    createShippingOrder,
    getAvailableServices,
    getShippingFee,
    getLeadTime
  };

  return <Context.Provider value={state}>{children}</Context.Provider>;
}

Provider.propTypes = {
  children: PropTypes.node
};

export default Provider;
