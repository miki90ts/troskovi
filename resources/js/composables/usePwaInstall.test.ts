import { beforeEach, describe, expect, it, vi } from 'vitest';
import {
    initializePwa,
    isIosDevice,
    isStandaloneDisplayMode,
    resetPwaStateForTests,
    usePwaInstall,
} from './usePwaInstall';

beforeEach(() => {
    resetPwaStateForTests();
    Object.defineProperty(window, 'matchMedia', {
        configurable: true,
        value: vi.fn().mockReturnValue({ matches: false }),
    });
});

describe('PWA installation state', () => {
    it('detects iOS and returns manual installation instructions', async () => {
        vi.spyOn(window.navigator, 'userAgent', 'get').mockReturnValue(
            'iPhone',
        );

        expect(isIosDevice(window.navigator)).toBe(true);
        initializePwa(true);

        const install = usePwaInstall();
        expect(install.canInstall.value).toBe(true);
        await expect(install.requestInstall()).resolves.toBe('instructions');
    });

    it('uses the deferred Chromium install prompt only while available', async () => {
        vi.spyOn(window.navigator, 'userAgent', 'get').mockReturnValue(
            'Chrome',
        );
        const prompt = vi.fn().mockResolvedValue(undefined);
        const event = Object.assign(new Event('beforeinstallprompt'), {
            prompt,
            userChoice: Promise.resolve({
                outcome: 'accepted' as const,
                platform: 'web',
            }),
        });

        initializePwa(true);
        const install = usePwaInstall();
        expect(install.canInstall.value).toBe(false);

        window.dispatchEvent(event);
        expect(install.canInstall.value).toBe(true);
        await expect(install.requestInstall()).resolves.toBe('prompted');
        expect(prompt).toHaveBeenCalledOnce();
        expect(install.canInstall.value).toBe(false);
    });

    it('hides installation when already running standalone', () => {
        Object.defineProperty(window, 'matchMedia', {
            configurable: true,
            value: vi.fn().mockReturnValue({ matches: true }),
        });

        expect(isStandaloneDisplayMode(window, window.navigator)).toBe(true);
        initializePwa(true);
        expect(usePwaInstall().canInstall.value).toBe(false);
    });
});
