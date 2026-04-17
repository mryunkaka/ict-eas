export async function compressImageFile(file, {
    maxBytes = 700 * 1024,
    maxDimension = 1920,
    outputType = 'image/jpeg',
    initialQuality = 0.9,
    minQuality = 0.35,
} = {}) {
    if (!file || !file.type?.startsWith('image/')) {
        return file;
    }

    if (file.size <= maxBytes) {
        return file;
    }

    const image = await loadImage(file);
    let width = image.width;
    let height = image.height;

    if (width > maxDimension || height > maxDimension) {
        const scale = Math.min(maxDimension / width, maxDimension / height);
        width = Math.max(1, Math.round(width * scale));
        height = Math.max(1, Math.round(height * scale));
    }

    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { alpha: false });
    if (!context) {
        return file;
    }

    let quality = initialQuality;
    let blob = null;

    for (let attempt = 0; attempt < 12; attempt += 1) {
        canvas.width = width;
        canvas.height = height;
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, width, height);
        context.drawImage(image, 0, 0, width, height);

        blob = await canvasToBlob(canvas, outputType, quality);
        if (blob && blob.size <= maxBytes) {
            break;
        }

        if (quality > minQuality) {
            quality = Math.max(minQuality, quality - 0.1);
        } else {
            width = Math.max(480, Math.round(width * 0.82));
            height = Math.max(480, Math.round(height * 0.82));
            quality = 0.75;
        }
    }

    if (!blob) {
        return file;
    }

    const outputName = `${file.name.replace(/\.[^.]+$/, '')}.jpg`;
    return new File([blob], outputName, { type: outputType, lastModified: Date.now() });
}

export function wireAutoCompressImageUploads({
    selector = 'input[type="file"][data-auto-compress-image="1"]',
    maxBytes = 700 * 1024,
} = {}) {
    const inputs = Array.from(document.querySelectorAll(selector));

    inputs.forEach((input) => {
        input.addEventListener('change', async (event) => {
            const target = event.target;
            const [file] = target.files ?? [];

            if (!file || !file.type?.startsWith('image/')) {
                return;
            }

            try {
                const compressed = await compressImageFile(file, { maxBytes });
                if (!compressed || compressed === file) {
                    return;
                }

                const dt = new DataTransfer();
                dt.items.add(compressed);
                target.files = dt.files;
            } catch (error) {
                // silent fallback: keep original file
            }
        });
    });
}

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        const objectUrl = URL.createObjectURL(file);

        image.onload = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(image);
        };

        image.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Failed to read image.'));
        };

        image.src = objectUrl;
    });
}

function canvasToBlob(canvas, type, quality) {
    return new Promise((resolve) => {
        canvas.toBlob(resolve, type, quality);
    });
}

