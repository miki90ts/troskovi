export const MAX_RECEIPT_SOURCE_SIZE = 12 * 1024 * 1024;
export const MAX_RECEIPT_UPLOAD_SIZE = 5 * 1024 * 1024;
export const TARGET_RECEIPT_SIZE = 2 * 1024 * 1024;
export const MAX_RECEIPT_DIMENSION = 2400;

export function fitReceiptDimensions(
    width: number,
    height: number,
    maximum = MAX_RECEIPT_DIMENSION,
): { width: number; height: number } {
    if (width <= maximum && height <= maximum) {
        return { width, height };
    }

    const scale = maximum / Math.max(width, height);

    return {
        width: Math.round(width * scale),
        height: Math.round(height * scale),
    };
}

function canvasToBlob(
    canvas: HTMLCanvasElement,
    quality: number,
): Promise<Blob> {
    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (blob) {
                    resolve(blob);
                } else {
                    reject(new Error('Slika nije mogla da bude obrađena.'));
                }
            },
            'image/jpeg',
            quality,
        );
    });
}

async function loadImage(file: File): Promise<ImageBitmap | HTMLImageElement> {
    if ('createImageBitmap' in globalThis) {
        return createImageBitmap(file, { imageOrientation: 'from-image' });
    }

    const objectUrl = URL.createObjectURL(file);

    try {
        const image = new Image();
        image.src = objectUrl;
        await image.decode();

        return image;
    } finally {
        URL.revokeObjectURL(objectUrl);
    }
}

export async function prepareReceiptImage(file: File): Promise<File> {
    if (!file.type.startsWith('image/')) {
        throw new Error('Izabrani fajl mora biti slika.');
    }

    if (file.size > MAX_RECEIPT_SOURCE_SIZE) {
        throw new Error('Slika ne sme biti veća od 12 MB.');
    }

    const image = await loadImage(file);
    const sourceWidth =
        'naturalWidth' in image ? image.naturalWidth : image.width;
    const sourceHeight =
        'naturalHeight' in image ? image.naturalHeight : image.height;
    const dimensions = fitReceiptDimensions(sourceWidth, sourceHeight);
    const canvas = document.createElement('canvas');
    canvas.width = dimensions.width;
    canvas.height = dimensions.height;

    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Browser ne podržava obradu slike.');
    }

    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(image, 0, 0, canvas.width, canvas.height);

    if ('close' in image && typeof image.close === 'function') {
        image.close();
    }

    let blob: Blob | null = null;

    for (const quality of [0.82, 0.76, 0.7, 0.65]) {
        blob = await canvasToBlob(canvas, quality);

        if (blob.size <= TARGET_RECEIPT_SIZE) {
            break;
        }
    }

    if (!blob || blob.size > MAX_RECEIPT_UPLOAD_SIZE) {
        throw new Error('Obrađena slika je i dalje prevelika.');
    }

    return new File([blob], `receipt-${Date.now()}.jpg`, {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}
