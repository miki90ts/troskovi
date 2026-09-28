<?php

test('PWA metadata stays hidden while the feature flag is disabled', function () {
    config()->set('features.pwa_install', false);

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('manifest.webmanifest', false)
        ->assertDontSee('apple-mobile-web-app-capable', false);
});

test('PWA metadata is rendered when the feature flag is enabled', function () {
    config()->set('features.pwa_install', true);

    $this->get('/login')
        ->assertOk()
        ->assertSee('rel="manifest"', false)
        ->assertSee('/manifest.webmanifest', false)
        ->assertSee('apple-mobile-web-app-capable', false);
});
