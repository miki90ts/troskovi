import vue from '@vitejs/plugin-vue';
import { resolve } from 'node:path';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': resolve(process.cwd(), 'resources/js'),
        },
    },
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.ts'],
        restoreMocks: true,
    },
});
