import './bootstrap';
import {createRouter, createWebHashHistory, createWebHistory} from 'vue-router'
import { createApp } from 'vue'
import App from "./App.vue";
import Home from "./layouts/Home.vue";
import About from "./components/About.vue";
import vuetify_plugin from "./plugins/vuetify";
import StorageViewer from "./layouts/StorageViewer.vue";
import StorageSelector from "./layouts/StorageSelector.vue";

const app  = createApp(App);

// Build the router
const routes = [
    { path: '/', component: Home },
    { path: '/about', component: About },
    { path: '/storage/', component: StorageSelector },
    { path: '/storage/:storageId/:storagePath(.*)', component: StorageViewer },
]
const router = createRouter({
    history: createWebHistory(),
    routes,
})
app.use(vuetify_plugin);
app.use(router);

app.mount('#app');
