import React from 'react';
import { Notice } from '@wordpress/components';

function RequestError({ errors }) {
  if (!errors) {
    return null;
  }

  let message = errors instanceof Error || errors.message
    ? errors.message
    : String(errors);

  return <Notice status="warning" isDismissible={false}>
    {message}
  </Notice>;
}

export default RequestError;
