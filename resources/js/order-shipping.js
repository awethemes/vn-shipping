import { createApp } from 'vue';

import App from './vue/App';

createApp({ template: '<app/>' })
  .component('App', App)
  .mount('#VNShippingRoot');
