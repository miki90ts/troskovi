import { describe, expect, it, vi } from 'vitest';
import { stopMediaStream } from './media';

describe('stopMediaStream', () => {
    it('stops every active video and audio track', () => {
        const first = { stop: vi.fn() };
        const second = { stop: vi.fn() };
        const stream = {
            getTracks: () => [first, second],
        } as unknown as MediaStream;

        stopMediaStream(stream);

        expect(first.stop).toHaveBeenCalledOnce();
        expect(second.stop).toHaveBeenCalledOnce();
    });
});
