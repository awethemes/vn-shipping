import { find } from 'lodash';
import { __ } from '@wordpress/i18n';

export const COURIERS = [
  /*{
    id: 'viettel_post',
    name: __('Viettel Post', 'vn-shipping'),
    icon: '/resources/icons/vtp.png'
  },*/
  {
    id: 'giao_hang_nhanh',
    name: __('Giao Hàng Nhanh', 'vn-shipping'),
    icon: '/resources/icons/ghn.png'
  },
  /*{
    id: 'giao_hang_tiet_kiem',
    name: __('Giao Hàng Tiết Kiệm', 'vn-shipping'),
    icon: '/resources/icons/ghtk.png'
  }*/
];

/**
 * @param {String} id
 * @returns {Object|undefined}
 */
export const getCourier = (id) => {
  const alias = {
    'ghn': 'giao_hang_nhanh',
    'ghtk': 'giao_hang_tiet_kiem',
    'vtp': 'viettel_post'
  };

  if (alias.hasOwnProperty(id)) {
    id = alias[id];
  }

  return find(COURIERS, { id });
};
