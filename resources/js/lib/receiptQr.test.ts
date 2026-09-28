import { describe, expect, it } from 'vitest';
import { normalizeFiscalVerificationUrl } from './receiptQr';

describe('normalizeFiscalVerificationUrl', () => {
    it.each([
        'https://suf.purs.gov.rs/v/?vl=valid-payload',
        'https://tap.suf.purs.gov.rs/v?vl=valid-payload',
    ])('accepts an official fiscal verification URL: %s', (url) => {
        expect(normalizeFiscalVerificationUrl(url)).toBe(url);
    });

    it.each([
        'http://suf.purs.gov.rs/v/?vl=payload',
        'https://suf.purs.gov.rs.evil.example/v/?vl=payload',
        'https://suf.purs.gov.rs/other/?vl=payload',
        'https://suf.purs.gov.rs/v/',
        'https://user:pass@suf.purs.gov.rs/v/?vl=payload',
        'https://suf.purs.gov.rs/v/?vl=payload#fragment',
        'not-a-url',
    ])('rejects an unsafe or unrelated URL: %s', (url) => {
        expect(normalizeFiscalVerificationUrl(url)).toBeNull();
    });

    it('rejects links longer than 8192 characters', () => {
        const url = `https://suf.purs.gov.rs/v/?vl=${'x'.repeat(8192)}`;

        expect(normalizeFiscalVerificationUrl(url)).toBeNull();
    });
});
