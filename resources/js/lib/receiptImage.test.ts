import { describe, expect, it } from 'vitest';
import { fitReceiptDimensions, prepareReceiptImage } from './receiptImage';

describe('receipt image preparation', () => {
    it('limits the longest side to 2400 pixels', () => {
        expect(fitReceiptDimensions(4800, 3200)).toEqual({
            width: 2400,
            height: 1600,
        });
        expect(fitReceiptDimensions(1200, 1600)).toEqual({
            width: 1200,
            height: 1600,
        });
    });

    it('rejects a source image larger than 12 MB before decoding', async () => {
        const file = new File(
            [new Uint8Array(12 * 1024 * 1024 + 1)],
            'large.jpg',
            {
                type: 'image/jpeg',
            },
        );

        await expect(prepareReceiptImage(file)).rejects.toThrow('12 MB');
    });
});
