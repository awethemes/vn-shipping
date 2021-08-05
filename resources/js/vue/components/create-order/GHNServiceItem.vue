<template>
  <input
    type="radio"
    name="service_id"
    :value="service.service_id"
    :id="`service_${service.service_id}`"
    @change="$emit('service-selected', service)"
  />

  <label :for="`service_${service.service_id}`">
    <p><strong>{{ service.short_name }}</strong></p>

    <p v-if="feeInfo && feeInfo.total">
      {{ formatCurrency(feeInfo.total) }}
    </p>

    <p v-if="leadTimeInfo && leadTimeInfo.leadtime">
      Ngày giao dự kiến: {{ formatDateString(leadTimeInfo.leadtime) }}
    </p>
  </label>
</template>

<script>
import { FormattingMixin } from '../../api';

export default {
  name: 'GhnServiceItem',

  mixins: [FormattingMixin],

  props: ['service', 'feeInfo', 'leadTimeInfo']
};
</script>
