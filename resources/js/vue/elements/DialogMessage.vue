<template>
  <modal
    v-if="isOpen"
    :title="title"
    @modal-close="isOpen = false">
    <div class="vns_confirm-dialog__message">
      <p v-if="messageForDisplay">{{ messageForDisplay }}</p>
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
  name: 'ConfirmDialog',

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
      return this.runtimeMessage || this.message || '';
    }
  }
};
</script>

<style scoped lang="scss">
.vns_confirm-dialog__message {
  box-sizing: border-box;
  padding-bottom: 1rem;
}

.vns_confirm-dialog__actions {
  display: flex;
  border-top: 1px solid #eee;
  margin: 0 -32px -24px;
  box-sizing: border-box;

  > [type="button"] {
    color: #444;
    font-size: 13px;
    background: #fff;
    font-weight: 700;
    line-height: 50px;
    flex: 1 1;
    border: 0;
    cursor: pointer;
    padding: 0 10px;
    user-select: none;
    white-space: nowrap;
    box-sizing: border-box;

    &:focus {
      outline: none;
      background: #f2f2f2;
      transition: background-color 0.24s ease-out;
    }

    &:first-child {
      border-bottom-left-radius: 12px;
    }

    &:last-child {
      color: #0062a9;
      border-left: 1px solid #eee;
      border-bottom-right-radius: 12px;
    }
  }
}
</style>
