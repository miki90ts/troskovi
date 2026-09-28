import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ReceiptQrScannerDialog from './ReceiptQrScannerDialog.vue';

const zxing = vi.hoisted(() => ({
    decodeFromConstraints: vi.fn(),
    decodeFromImageUrl: vi.fn(),
}));

vi.mock('@zxing/browser', () => ({
    BrowserQRCodeReader: class {
        decodeFromConstraints = zxing.decodeFromConstraints;
        decodeFromImageUrl = zxing.decodeFromImageUrl;
    },
}));

const passthrough = { template: '<div><slot /></div>' };

function mountScanner() {
    return mount(ReceiptQrScannerDialog, {
        props: { open: false },
        global: {
            stubs: {
                Dialog: passthrough,
                DialogContent: passthrough,
                DialogDescription: passthrough,
                DialogFooter: passthrough,
                DialogHeader: passthrough,
                DialogTitle: passthrough,
                Button: { template: '<button><slot /></button>' },
                Input: { template: '<input />' },
            },
        },
    });
}

beforeEach(() => {
    zxing.decodeFromConstraints.mockReset();
    zxing.decodeFromImageUrl.mockReset();
    vi.spyOn(window, 'open').mockImplementation(() => null);
});

describe('ReceiptQrScannerDialog camera lifecycle', () => {
    it('handles a rejected camera permission without throwing', async () => {
        zxing.decodeFromConstraints.mockRejectedValue(
            new DOMException('denied', 'NotAllowedError'),
        );
        const wrapper = mountScanner();

        await wrapper.setProps({ open: true });
        await flushPromises();

        expect(wrapper.text()).toContain('Kamera nije dostupna');
        expect(wrapper.emitted('scanned')).toBeUndefined();
    });

    it('emits a valid URL and stops every stream and scanner track', async () => {
        const track = { stop: vi.fn() };
        const controls = { stop: vi.fn() };
        const url = 'https://suf.purs.gov.rs/v/?vl=valid-payload';

        zxing.decodeFromConstraints.mockImplementation(
            async (_constraints, video: HTMLVideoElement, callback) => {
                Object.defineProperty(video, 'srcObject', {
                    configurable: true,
                    writable: true,
                    value: { getTracks: () => [track] },
                });
                callback({ getText: () => url });

                return controls;
            },
        );

        const wrapper = mountScanner();
        await wrapper.setProps({ open: true });
        await flushPromises();

        expect(wrapper.emitted('scanned')).toEqual([[url]]);
        expect(zxing.decodeFromConstraints).toHaveBeenCalledWith(
            expect.objectContaining({
                video: { facingMode: { ideal: 'environment' } },
            }),
            expect.any(HTMLVideoElement),
            expect.any(Function),
        );
        expect(track.stop).toHaveBeenCalledOnce();
        expect(controls.stop).toHaveBeenCalledOnce();
        expect(window.open).toHaveBeenCalledWith(
            url,
            '_blank',
            'noopener,noreferrer',
        );
    });
});
