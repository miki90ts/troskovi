import { computed, ref } from 'vue';

export interface BeforeInstallPromptEvent extends Event {
    prompt: () => Promise<void>;
    userChoice: Promise<{
        outcome: 'accepted' | 'dismissed';
        platform: string;
    }>;
}

const enabled = ref(false);
const installed = ref(false);
const iosDevice = ref(false);
const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null);
let initialized = false;

export function isIosDevice(navigatorValue: Navigator): boolean {
    return (
        /iPad|iPhone|iPod/.test(navigatorValue.userAgent) ||
        (navigatorValue.platform === 'MacIntel' &&
            navigatorValue.maxTouchPoints > 1)
    );
}

export function isStandaloneDisplayMode(
    windowValue: Window,
    navigatorValue: Navigator,
): boolean {
    const iosNavigator = navigatorValue as Navigator & { standalone?: boolean };

    return (
        windowValue.matchMedia('(display-mode: standalone)').matches ||
        iosNavigator.standalone === true
    );
}

export function initializePwa(isEnabled: boolean): void {
    enabled.value = isEnabled;

    if (!isEnabled || initialized || typeof window === 'undefined') {
        return;
    }

    initialized = true;
    installed.value = isStandaloneDisplayMode(window, navigator);
    iosDevice.value = isIosDevice(navigator);

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt.value = event as BeforeInstallPromptEvent;
    });

    window.addEventListener('appinstalled', () => {
        installed.value = true;
        deferredPrompt.value = null;
    });

    const localHostnames = new Set(['localhost', '127.0.0.1', '[::1]']);
    const canRegister =
        window.isSecureContext || localHostnames.has(window.location.hostname);

    if ('serviceWorker' in navigator && canRegister) {
        void navigator.serviceWorker.register('/sw.js').catch(() => {
            // Instalacija ostaje opciona; greška service workera ne sme oboriti aplikaciju.
        });
    }
}

export function usePwaInstall() {
    const canInstall = computed(
        () =>
            enabled.value &&
            !installed.value &&
            (deferredPrompt.value !== null || iosDevice.value),
    );

    async function requestInstall(): Promise<
        'prompted' | 'instructions' | 'unavailable'
    > {
        if (iosDevice.value && deferredPrompt.value === null) {
            return 'instructions';
        }

        const prompt = deferredPrompt.value;

        if (!prompt) {
            return 'unavailable';
        }

        await prompt.prompt();
        await prompt.userChoice;
        deferredPrompt.value = null;

        return 'prompted';
    }

    return {
        canInstall,
        installed: computed(() => installed.value),
        requestInstall,
    };
}

export function resetPwaStateForTests(): void {
    enabled.value = false;
    installed.value = false;
    iosDevice.value = false;
    deferredPrompt.value = null;
    initialized = false;
}
