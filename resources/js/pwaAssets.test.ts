import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { describe, expect, it } from 'vitest';

describe('PWA static assets', () => {
    it('uses an online-only installable manifest', () => {
        const manifest = JSON.parse(
            readFileSync(resolve('public/manifest.webmanifest'), 'utf8'),
        );

        expect(manifest.name).toBe('Troškovi');
        expect(manifest.short_name).toBe('Troškovi');
        expect(manifest.start_url).toBe('/dashboard');
        expect(manifest.scope).toBe('/');
        expect(manifest.display).toBe('standalone');
        expect(manifest.icons).toEqual(
            expect.arrayContaining([
                expect.objectContaining({ sizes: '192x192' }),
                expect.objectContaining({ sizes: '512x512' }),
                expect.objectContaining({ purpose: 'maskable' }),
            ]),
        );
    });

    it('does not intercept requests or create a cache', () => {
        const serviceWorker = readFileSync(resolve('public/sw.js'), 'utf8');

        expect(serviceWorker).not.toContain("addEventListener('fetch'");
        expect(serviceWorker).not.toContain('caches.');
    });

    it('declares the manifest MIME type and disables service worker caching', () => {
        const apache = readFileSync(resolve('public/.htaccess'), 'utf8');

        expect(apache).toContain('application/manifest+json');
        expect(apache).toContain('no-cache, no-store, must-revalidate');
        expect(apache).toContain('Service-Worker-Allowed "/"');
    });

    it.each([
        ['apple-touch-icon.png', 180],
        ['pwa-192x192.png', 192],
        ['pwa-512x512.png', 512],
        ['pwa-maskable-512x512.png', 512],
    ])('provides %s at the declared square size', (filename, size) => {
        const png = readFileSync(resolve('public', filename));

        expect(png.subarray(1, 4).toString()).toBe('PNG');
        expect(png.readUInt32BE(16)).toBe(size);
        expect(png.readUInt32BE(20)).toBe(size);
    });
});
