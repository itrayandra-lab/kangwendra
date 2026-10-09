/* Shrinks images in the browser before upload so they stay under the server's PHP upload limit (2 MB). */
(function () {
    var MAX_SIDE = 2000;
    var TARGET_BYTES = 1.5 * 1024 * 1024;

    function load(file) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }

    function toBlob(canvas, quality) {
        return new Promise(function (resolve) { canvas.toBlob(resolve, 'image/webp', quality); });
    }

    async function compress(file) {
        if (!file || !/^image\/(png|jpe?g|webp)$/i.test(file.type)) return file;
        if (file.size <= TARGET_BYTES) return file;

        var img = await load(file);
        var scale = Math.min(1, MAX_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
        var canvas = document.createElement('canvas');
        canvas.width = Math.round(img.naturalWidth * scale);
        canvas.height = Math.round(img.naturalHeight * scale);
        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
        URL.revokeObjectURL(img.src);

        var blob = null;
        for (var q = 0.86; q >= 0.5; q -= 0.08) {
            blob = await toBlob(canvas, q);
            if (blob && blob.size <= TARGET_BYTES) break;
        }
        if (!blob || blob.size >= file.size) return file;

        var name = file.name.replace(/\.[^.]+$/, '') + '.webp';
        return new File([blob], name, { type: 'image/webp', lastModified: Date.now() });
    }

    window.lunarayCompressImage = compress;

    // Replace the chosen file in any image <input type="file"> with the compressed version.
    document.addEventListener('change', async function (e) {
        var input = e.target;
        if (!input.matches || !input.matches('input[type="file"][accept^="image"]') || !input.files.length) return;
        if (input.dataset.compressing) return;

        var original = input.files[0];
        var smaller = await compress(original);
        if (smaller === original) return;

        var dt = new DataTransfer();
        dt.items.add(smaller);
        input.dataset.compressing = '1';
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        delete input.dataset.compressing;
    }, true);
})();
