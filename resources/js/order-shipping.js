import { createApp } from 'vue';

import App from './vue/App';

import Modal from './vue/elements/Modal';
import Loading from './vue/elements/Loading';
import WooSelect from './vue/elements/WooSelect';
import AddressField from './vue/elements/AddressField';

createApp({ template: '<app/>' })
  .component('App', App)
  .component('Modal', Modal)
  .component('Loading', Loading)
  .component('WooSelect', WooSelect)
  .component('AddressField', AddressField)
  .mount('#VNShippingRoot');
