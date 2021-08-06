<template>
  <select :id="id" :name="name" :disabled="disabled" :required="required"></select>
</template>

<script>
import $ from 'jquery';

const castOptions = (options) => {
  if (!Array.isArray(options)) {
    return [];
  }

  return Array
    .from(options || [])
    .map(opt => ({ id: opt.value, text: opt.label }));
};

export default {
  name: 'WooSelect',

  emits: ['update:modelValue'],

  props: [
    'id',
    'name',
    'placeholder',
    'options',
    'disabled',
    'required',
    'modelValue'
  ],

  mounted() {
    $(this.$el)
      .selectWoo({
        width: '100%',
        placeholder: this.placeholder,
        data: castOptions(this.options)
      })
      .on('select2:select select2:unselect', ev => {
        this.$emit('update:modelValue', $(this.$el).val());
        this.$emit('select', ev['params']['data']);
      });

    this.setValue(this.modelValue);
  },

  beforeUnmount() {
    $(this.$el)
      .off()
      .selectWoo('destroy');
  },

  watch: {
    options: {
      deep: true,
      handler(options) {
        this.setOptions(options);
      }
    },

    modelValue: {
      deep: true,
      handler(value) {
        this.setValue(value);
      }
    }
  },

  methods: {
    /**
     * Set selected value.
     *
     * @param {*} value
     */
    setValue(value) {
      const element = $(this.$el);

      if (value instanceof Array) {
        element.val([...value]);
      } else {
        element.val([value]);
      }

      element.trigger('change');
    },

    /**
     * Set the options.
     *
     * @param {Array} options
     */
    setOptions(options = []) {
      const element = $(this.$el);

      element.empty();

      element.selectWoo({
        width: '100%',
        placeholder: this.placeholder,
        data: castOptions(options)
      });

      this.setValue(this.modelValue);
    }
  }
};
</script>
