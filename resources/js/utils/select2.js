import $ from 'jquery';

/**
 * @returns {Object}
 */
const getEnhancedSelectFormatString = () => {
  const params = window.wc_enhanced_select_params || window.wc_country_select_params || null;

  if (!params) {
    return {};
  }

  return {
    'language': {
      // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
      errorLoading: () => params.i18n_searching,

      noResults: () => params.i18n_no_matches,
      searching: () => params.i18n_searching,
      loadingMore: () => params.i18n_load_more,

      inputTooLong(args) {
        let overChars = args.input.length - args.maximum;

        return (1 === overChars)
          ? params.i18n_input_too_long_1
          : params.i18n_input_too_long_n.replace('%qty%', overChars);
      },

      inputTooShort(args) {
        let remainingChars = args.minimum - args.input.length;

        return (1 === remainingChars)
          ? params.i18n_input_too_short_1
          : params.i18n_input_too_short_n.replace('%qty%', remainingChars);
      },

      maximumSelected(args) {
        return args.maximum === 1
          ? params.i18n_selection_too_long_1
          : params.i18n_selection_too_long_n.replace('%qty%', args.maximum);
      }
    }
  };
};

/**
 * @param {HTMLSelectElement} element
 * @return {Object|void}
 */
export function createSelect2(element) {
  if (!$.fn.selectWoo) {
    console.warn('Warning: $.fn.selectWoo is not defined');
    return;
  }

  const select = $(element);

  if (!select.hasClass('select2-hidden-accessible')) {
    select.on('select2:select', () => select.focus());

    select.selectWoo({
      ...getEnhancedSelectFormatString(),
      placeholder: select.attr('data-placeholder') || select.attr('placeholder'),
      width: '100%'
    });
  }

  return {
    value() {
      return element.value;
    },

    destroy() {
      select.selectWoo('destroy');
    },

    disable(disabled = true) {
      select.prop('disabled', disabled);
    },

    clearOptions() {
      element.innerHTML = '';
      element.value = null;

      select.trigger('change.select2');
    },

    setOptions(options, defaultValue) {
      element.innerHTML = '';
      // element.appendChild(new Option('', ''));

      const hasDefaultValue = typeof defaultValue !== 'undefined'
        || String(defaultValue).trim() !== '';

      Array.from(options).forEach((data) => {
        element.appendChild(new Option(
          data.label,
          data.value,
          hasDefaultValue ? String(data.value) === String(defaultValue) : false
        ));
      });

      if (hasDefaultValue) {
        element.value = defaultValue;
      }

      select.trigger('change.select2');
    }
  };
}
