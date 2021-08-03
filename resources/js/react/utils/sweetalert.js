import Swal from 'sweetalert2/dist/sweetalert2';

export const Toast = (options = {}) => {
  return Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    animation: true,
    timer: 15000,
    ...options
  });
};

export const SwalConfirm = (message, options = {}) => {
  if (typeof message === 'object') {
    [options, message] = [message, null];
  }

  return Swal.fire({
    text: message || 'Are you sure you want to perform this action?',
    position: 'center',
    backdrop: 'rgba(0,0,0,.8)',
    reverseButtons: true,
    buttonsStyling: false,
    showCancelButton: true,
    customClass: {
      popup: 'Swal2ConfirmDialog',
      cancelButton: '',
      confirmButton: ''
    },
    ...options
  });
};

export const SwalAlert = (message, options = {}) => {
  if (typeof message === 'object') {
    [options, message] = [message, null];
  }

  return Swal.fire({
    text: message,
    icon: 'success',
    position: 'center',
    backdrop: 'rgba(0,0,0,.8)',
    reverseButtons: true,
    buttonsStyling: false,
    showCancelButton: false,
    customClass: {
      popup: 'Swal2ConfirmDialog Swal2ConfirmDialog--alert',
      cancelButton: '',
      confirmButton: ''
    },
    ...options
  });
};
