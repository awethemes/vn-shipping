import React from 'react';
import { filter } from 'lodash';

import styled from 'styled-components';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { NoticeList, SnackbarList } from '@wordpress/components';
import { createPortal } from '@wordpress/element';

const NoticeWrapper = styled.div`
  position: fixed;
  right: 1rem;
  bottom: 1rem;
  padding-left: 1rem;
  width: auto !important;
  z-index: 999999;

  @media (max-width: 960px) {
    left: 50px;
  }
`;

function TransientNotices({ notices, onRemove }) {
  const defaultNotices = filter(notices, {
    type: 'default'
  });

  const snackbarNotices = filter(notices, {
    type: 'snackbar'
  });

  return createPortal(
    <NoticeWrapper>
      <SnackbarList
        notices={snackbarNotices}
        className="components-notices__snackbar"
        onRemove={onRemove}
      />

      <NoticeList
        notices={defaultNotices}
        className="components-notices__pinned"
        onRemove={onRemove}
      />
    </NoticeWrapper>,
    document.body
  );
}

export default compose(
  withSelect((select) => ({
    notices: select('core/notices').getNotices('awethemes')
  })),
  withDispatch((dispatch) => ({
    onRemove: dispatch('core/notices').removeNotice
  }))
)(TransientNotices);
