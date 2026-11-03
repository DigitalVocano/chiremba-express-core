require('./bootstrap');

window.$ = window.jQuery = require('jquery'); // Make jQuery globally available

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();
