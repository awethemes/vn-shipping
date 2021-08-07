<template>
  <div :class="`vns-component-notice ${stateClass}`">
    <p>{{ errorMessage }}</p>
  </div>
</template>

<script>
export default {
  name: 'Notice',

  props: ['error', 'isError'],

  computed: {
    stateClass() {
      return this.isError ? 'is-error' : 'is-warning';
    },

    errorMessage() {
      if (this.error instanceof Error) {
        return this.error.message;
      }

      return String(this.error);
    }
  }
};
</script>
