import React from 'react';
import { Modal as BaseModal } from '@wordpress/components';

/**
 * Internal dependencies
 */
import Loading from './Loading';

function Modal({
  title,
  onRequestClose,
  isOpen = false,
  isLoading = false,
  children
}) {
  if (!isOpen) {
    return null;
  }

  return (
    <BaseModal
      title={title}
      onRequestClose={onRequestClose}
      isDismissible={true}
      shouldCloseOnEsc={false}
      shouldCloseOnClickOutside={false}
    >
      {isLoading ? <Loading isLoading={true} /> : children}
    </BaseModal>
  );
}

export default Modal;
