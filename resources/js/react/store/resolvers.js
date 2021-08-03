import { apiFetch } from '@wordpress/data-controls';

import * as actions from './actions';

export function* getProvinces() {
  const data = yield apiFetch({ path: '/awethemes/vn-shipping/address' });

  return actions.setProvinceData(data);
}
