export const MAX_FISCAL_VERIFICATION_URL_LENGTH = 8192;

export function normalizeFiscalVerificationUrl(value: string): string | null {
    const candidate = value.trim();

    if (
        candidate.length === 0 ||
        candidate.length > MAX_FISCAL_VERIFICATION_URL_LENGTH
    ) {
        return null;
    }

    try {
        const url = new URL(candidate);
        const hostname = url.hostname.toLowerCase().replace(/\.$/, '');
        const trustedHost =
            hostname === 'suf.purs.gov.rs' ||
            hostname.endsWith('.suf.purs.gov.rs');
        const verificationPath = url.pathname.replace(/\/+$/, '') === '/v';
        const verificationValues = url.searchParams.getAll('vl');
        const verificationValue = verificationValues.at(-1);

        if (
            url.protocol !== 'https:' ||
            url.username !== '' ||
            url.password !== '' ||
            url.hash !== '' ||
            !trustedHost ||
            !verificationPath ||
            !verificationValue
        ) {
            return null;
        }

        return url.toString();
    } catch {
        return null;
    }
}
