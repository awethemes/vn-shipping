import { useDispatch, useSelect } from '@wordpress/data';

import { STORE_NAME } from '../store/constants';

export function useConfig() {
  return useSelect(select => select(STORE_NAME).getConfig());
}

export function setInitialConfig(config) {
  useDispatch(STORE_NAME).setInitialConfig(config);
}
