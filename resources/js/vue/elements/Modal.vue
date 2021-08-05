<template>
  <teleport to="body">
    <div class="vns_modal__overlay" @keydown.esc="$emit('modalClose', $event)">

      <div class="vns_modal__frame" role="dialog" tabindex="-1" :id="modalId">
        <div class="vns_modal__content" role="document">
          <div class="vns_modal__header">
            <div class="vns_modal__header-heading-container">
              <h1 class="vns_modal__header-heading" v-if="title">{{ title }}</h1>
            </div>

            <button
              type="button"
              class="components-button has-icon"
              aria-label="Close dialog"
              @click.prevent="$emit('modalClose', $event);">
              <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false">
                <path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path>
              </svg>
            </button>
          </div>

          <slot />
          <slot name="actions" />
        </div>
      </div>

    </div>
  </teleport>
</template>

<script>
let instanceCount = 0;

export default {
  name: 'Modal',
  props: ['title'],
  emits: ['modalClose'],

  data() {
    return {
      instanceId: ++instanceCount
    };
  },

  computed: {
    modalId() {
      return `vns_dialog_${this.instanceId}`;
    }
  }
};
</script>

<style scoped lang="scss">
.vns_modal__overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.35);
  z-index: 100000;
}

.vns_modal__frame {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  box-sizing: border-box;
  margin: 0;
  background: #fff;
  box-shadow: 0 10px 10px rgba(0, 0, 0, 0.25);
  border-radius: 12px;
  overflow: auto;

  @media (min-width: 600px) {
    top: 50%;
    right: auto;
    bottom: auto;
    left: 50%;
    min-width: 360px;
    max-width: calc(100% - 16px - 16px);
    max-height: 90%;
    transform: translate(-50%, -50%);
  }
}

.vns_modal__header {
  box-sizing: border-box;
  border-bottom: 1px solid #ddd;
  padding: 0 32px;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  background: #fff;
  align-items: center;
  height: 60px;
  z-index: 10;
  position: relative;
  position: sticky;
  top: 0;
  margin: 0 -32px 24px;

  @supports (-ms-ime-align: auto) {
    position: fixed;
    width: 100%;
  }
}

.vns_modal__header {
  h1 {
    line-height: 1;
    margin: 0;
  }

  .components-button {
    position: relative;
    left: 8px;
  }
}

.vns_modal__header-heading {
  font-size: 1rem;
  font-weight: 600;
}

.vns_modal__header-heading-container {
  align-items: center;
  flex-grow: 1;
  display: flex;
  flex-direction: row;
  justify-content: left;
}

.vns_modal__content {
  box-sizing: border-box;
  height: 100%;
  padding: 0 32px 24px;
}

@supports (-ms-ime-align: auto) {
  .vns_modal__content {
    padding-top: 60px;
  }
}
</style>
