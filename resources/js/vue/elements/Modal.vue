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
