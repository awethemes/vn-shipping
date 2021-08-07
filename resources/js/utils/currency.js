import { sprintf } from '@wordpress/i18n';
import { numberFormat } from '@woocommerce/number';

const CurrencyFactory = (currencySetting) => {
  let currency;

  const defaultCurrency = {
    code: 'USD',
    symbol: '$',
    symbolPosition: 'left',
    thousandSeparator: ',',
    decimalSeparator: '.',
    precision: 2
  };

  setCurrency(currencySetting);

  function setCurrency(setting) {
    const config = { ...defaultCurrency, ...setting };

    currency = {
      code: config.code.toString(),
      symbol: config.symbol.toString(),
      symbolPosition: config.symbolPosition.toString(),
      decimalSeparator: config.decimalSeparator.toString(),
      priceFormat: getPriceFormat(config),
      thousandSeparator: config.thousandSeparator.toString(),
      precision: parseInt(config.precision, 10)
    };
  }

  function stripTags(str) {
    const tmp = document.createElement('DIV');
    tmp.innerHTML = str;
    return tmp.textContent || tmp.innerText || '';
  }

  /**
   * Formats money value.
   *
   * @param   {number|string} number number to format
   * @return {?string} A formatted string.
   */
  function formatAmount(number) {
    const formattedNumber = numberFormat(currency, number);

    if (formattedNumber === '') {
      return formattedNumber;
    }

    const { priceFormat, symbol } = currency;

    // eslint-disable-next-line @wordpress/valid-sprintf
    return sprintf(priceFormat, symbol, formattedNumber);
  }

  /**
   * Get the default price format from a currency.
   *
   * @param {Object} config Currency configuration.
   * @return {string} Price format.
   */
  function getPriceFormat(config) {
    if (config.priceFormat) {
      return stripTags(config.priceFormat.toString());
    }

    switch (config.symbolPosition) {
      case 'left':
        return '%1$s%2$s';
      case 'right':
        return '%2$s%1$s';
      case 'left_space':
        return '%1$s&nbsp;%2$s';
      case 'right_space':
        return '%2$s&nbsp;%1$s';
    }

    return '%1$s%2$s';
  }

  return {
    getCurrencyConfig: () => ({ ...currency }),
    setCurrency,
    formatAmount,
    getPriceFormat,

    /**
     * Get the rounded decimal value of a number at the precision used for the current currency.
     * This is a work-around for fraction-cents, meant to be used like `wc_format_decimal`
     *
     * @param {number|string} number A floating point number (or integer), or string that converts
     *   to a number
     * @return {number} The original number rounded to a decimal point
     */
    formatDecimal(number) {
      if (typeof number !== 'number') {
        number = parseFloat(number);
      }

      if (Number.isNaN(number)) {
        return 0;
      }

      const { precision } = currency;

      return (
        Math.round(number * Math.pow(10, precision)) / Math.pow(10, precision)
      );
    },

    /**
     * Get the string representation of a floating point number to the precision used by the
     * current currency. This is different from `formatAmount` by not returning the currency
     * symbol.
     *
     * @param  {number|string} number A floating point number (or integer), or string that converts
     *   to a number
     * @return {string} The original number rounded to a decimal point
     */
    formatDecimalString(number) {
      if (typeof number !== 'number') {
        number = parseFloat(number);
      }

      if (Number.isNaN(number)) {
        return '';
      }

      const { precision } = currency;
      return number.toFixed(precision);
    }
  };
};

export default CurrencyFactory;
