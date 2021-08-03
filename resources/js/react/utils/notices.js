import { dispatch } from '@wordpress/data';

export function addSnackbar(message, type) {
  addNotice(message, type, 'snackbar');
}

export function addNotice(message, type, noticeType = 'default') {
  const notices = dispatch('core/notices');

  switch (type) {
    case 'success':
      notices.createSuccessNotice(message, { type: noticeType, context: 'awethemes' });
      break;
    case 'error':
      notices.createErrorNotice(message, { type: noticeType, context: 'awethemes' });
      break;
    case 'warning':
      notices.createWarningNotice(message, { type: noticeType, context: 'awethemes' });
      break;
    case 'info':
      notices.createInfoNotice(message, { type: noticeType, context: 'awethemes' });
      break;
    default:
      notices.createNotice(type, message, { type: noticeType, context: 'awethemes' });
  }
}
