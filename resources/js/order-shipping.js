import React from 'react';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import './react/store';
import App from './react/App';

function getConfigFromElement(element) {
  if (!element.hasAttribute('data-config')) {
    return {};
  }

  try {
    return JSON.parse(element.getAttribute('data-config'));
  } catch (e) {
    console.error(e);

    return {};
  }
}

domReady(() => {
  const rootElement = document.getElementById('VNShippingRoot');

  if (!rootElement) {
    console.warn('No #VNShippingRoot element found in the document.');
    return;
  }

  const config = getConfigFromElement(rootElement);

  render(<App config={config} />, rootElement);
});
