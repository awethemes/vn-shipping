<template>
  <div :class="{ 'vns-block-ui-container': true, 'is-loading': isLoading, 'is-small': isSmall }">
    <div class="vns-block-ui-loading" v-if="isLoading">
      <loading />
    </div>

    <slot />
  </div>
</template>

<script>
import Loading from './Loading';

export default {
  name: 'BlockUi',

  components: { Loading },

  props: ['isLoading', 'isSmall']
};
</script>

<style scoped lang="scss">
.vns-block-ui-container {
  position: relative;
  z-index: 100;

  &.is-loading {
    user-select: none;
    pointer-events: none;
  }

  &.is-small .vns-loading {
    transform: scale(0.5);
  }
}

.vns-block-ui-loading {
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  background: rgb(255 255 255 / 75%);
  z-index: 105;
}
</style>
