'use strict';

globalThis.addEventListener('install', () => {
    void globalThis.skipWaiting();
});

globalThis.addEventListener('activate', (event) => {
    event.waitUntil(globalThis.clients.claim());
});
