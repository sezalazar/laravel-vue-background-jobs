import './bootstrap';
import { createApp } from 'vue';
import BackgroundJobRunner from './components/BackgroundJobRunner.vue/BackgroundJobRunner.vue';

createApp(BackgroundJobRunner).mount('#run_background_process');