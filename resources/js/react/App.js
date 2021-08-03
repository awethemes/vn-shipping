import React from 'react';

/**
 * Internal dependencies
 */
import { APIProvider } from './api';
import { setInitialConfig } from './config';

import Shipment from './components/Shipment';
import TransientNotices from './components/elements/TransientNotices';

function App({ config }) {
  setInitialConfig(config);

  return (
    <APIProvider>
      <Shipment />
      <TransientNotices />
    </APIProvider>
  );
}

export default App;
