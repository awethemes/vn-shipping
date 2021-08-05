<template>
  <select class="">
    <slot></slot>
  </select>
</template>

<script>
import $ from 'jquery';
import { createSelect2 } from '../../utils/select2';

const transformOptions = (options) => {
  if (!Array.isArray(options)) {
    return [];
  }

  return Array
    .from(options || [])
    .map(opt => ({ id: opt.value, text: opt.label }));
};

export default {
  name: 'WooSelect',

  props: ['options', 'modelValue'],

  mounted() {
    createSelect2(this.$el, {
      data: transformOptions(this.options)
    });

    $(this.$el)
      .val(this.modelValue)
      .trigger('change')
      .on('change', (e) => {
        this.$emit('update:modelValue', e.currentTarget.value);
      });
  },

  beforeUnmount() {
    $(this.$el)
      .off()
      .selectWoo('destroy');
  },

  watch: {
    modelValue(value) {
      $(this.$el)
        .val(value)
        .trigger('change');
    },

    options(options) {
      $(this.$el)
        .empty()
        .selectWoo({ data: transformOptions(options) })
        .trigger('change');
    }
  }
};
</script>
