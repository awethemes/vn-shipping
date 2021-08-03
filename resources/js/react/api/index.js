import React, { useContext } from 'react';

import { createHigherOrderComponent } from '@wordpress/compose';
import { Component, createElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import APIContext from './context';
import APIProvider from './provider';

function useAPI() {
  return useContext(APIContext);
}

function withAPI(getActions) {
  return createHigherOrderComponent((Component) => (props) => {
    const actions = getActions(useAPI());

    return (
      <Component{...props}{...actions} />
    );
  }, 'withAPI');
}

export {
  useAPI,
  withAPI,
  APIContext,
  APIProvider
};
