<template>
  <input
    type="radio"
    name="service_id"
    :disabled="!(feeInfo && leadTimeInfo)"
    :checked="isChecked"
    :value="service.service_id"
    :id="`service_${service.service_id}`"
    @change="$emit('service-selected', service)"
    required
  />

  <label :for="`service_${service.service_id}`">
    <p>
      <span>{{ service.short_name }}</span> -
      <strong v-if="feeInfo && feeInfo.total">{{ formatCurrency(feeInfo.total) }}</strong>
    </p>

    <p v-if="leadTimeInfo && leadTimeInfo.leadtime" style="color: #666">
      Ngày giao dự kiến: {{ formatDateString(leadTimeInfo.leadtime) }}
    </p>
  </label>
</template>

<script>
import { FormattingMixin } from '../../api';

export default {
  name: 'GhnServiceItem',

  mixins: [FormattingMixin],

  props: ['service', 'feeInfo', 'leadTimeInfo', 'isChecked'],

  emits: ['service-selected']
};
</script>
