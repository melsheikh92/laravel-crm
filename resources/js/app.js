import './bootstrap';

import { createApp } from 'vue';
import DocumentationSearch from './components/DocumentationSearch.vue';

// Create Vue app
const app = createApp({});

// Register components
app.component('documentation-search', DocumentationSearch);

// Mount the app
app.mount('#vue-app');
