<template>
  <modal
    v-if="isOpen"
    :title="title"
    @modal-close="isOpen = false">
    <div class="vns_confirm-dialog__message">
      <div v-if="messageForDisplay" v-html="messageForDisplay"></div>
      <slot />
    </div>

    <template v-slot:actions>
      <div class="vns_confirm-dialog__actions">
        <button type="button" @click.prevent="cancel" v-if="isConfirm">Đóng</button>
        <button type="button" @click.prevent="agree">OK</button>
      </div>
    </template>
  </modal>
</template>

<script>
import Modal from './Modal';

export default {
  name: 'DialogMessage',

  components: { Modal },

  props: ['title', 'message', 'isConfirm'],

  data: () => ({
    isOpen: false,
    runtimeMessage: null,
    resolve: null,
    reject: null
  }),

  methods: {
    open(message = null) {
      this.isOpen = true;
      this.runtimeMessage = message;

      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
      });
    },

    agree() {
      if (this.resolve) {
        this.resolve(true);
        this.isOpen = false;
      }
    },

    cancel() {
      if (this.resolve) {
        this.resolve(false);
        this.isOpen = false;
      }
    }
  },

  computed: {
    messageForDisplay() {
      const message = this.runtimeMessage || this.message || '';

      return message;
    }
  }
};
</script>
