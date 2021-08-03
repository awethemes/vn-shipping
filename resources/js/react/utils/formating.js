import moment from 'moment';
import { dateI18n } from '@wordpress/date';

/**
 * Internal dependencies
 */
import CurrencyFactory from './currency';

const currency = new CurrencyFactory({
  code: 'VND',
  symbol: 'đ',
  symbolPosition: 'right',
  thousandSeparator: ',',
  decimalSeparator: '.',
  precision: 0
});

/**
 * @param {Number} timestamp
 */
export function formatDateString(timestamp) {
  return dateI18n('d/m/Y', moment.unix(timestamp));
}

/**
 * @param {Number} number
 * @returns {?string}
 */
export function formatCurrency(number) {
  return currency.formatAmount(number);
}
