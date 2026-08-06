<?php
/**
 * ImageService
 * Converts an uploaded image file in place to WebP — same folder, same
 * basename, new extension — so every image the system stores (products,
 * variants, avatars, receipts, refund proof, GCash QR) ends up as .webp
 * regardless of what format the user actually uploaded.
 */
class ImageService {

    /**
     * @param string $absoluteDir  Directory the file lives in (e.g. FCPATH.'uploads/products/')
     * @param string $filename     The file's current name (e.g. from CI's upload library 'file_name')
     * @param int    $quality      WebP quality, 0-100
     * @return string|null The new filename (e.g. "abc123.webp") on success, or NULL if
     *                      conversion wasn't possible — callers should keep using the
     *                      original filename in that case rather than losing the upload.
     */
    public static function convertToWebp($absoluteDir, $filename, $quality = 82) {
        if (!function_exists('imagewebp')) {
            return NULL; // GD build without WebP support — leave the original as-is
        }

        $dir = rtrim($absoluteDir, '/\\') . DIRECTORY_SEPARATOR;
        $path = $dir . $filename;
        if (!is_file($path)) {
            return NULL;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'webp') {
            return $filename; // already webp, nothing to do
        }

        $image = self::_load($path, $ext);
        if (!$image) {
            return NULL;
        }

        // Preserve transparency (PNG/GIF) instead of it turning black/white.
        imagepalettetotruecolor($image);
        imagealphablending($image, TRUE);
        imagesavealpha($image, TRUE);

        $newFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $newPath = $dir . $newFilename;

        $ok = imagewebp($image, $newPath, $quality);
        imagedestroy($image);

        if (!$ok || !is_file($newPath) || filesize($newPath) === 0) {
            if (is_file($newPath)) {
                @unlink($newPath);
            }
            return NULL;
        }

        @unlink($path);
        return $newFilename;
    }

    /**
     * Same as convertToWebp() but takes/returns a full relative path (e.g.
     * "uploads/products/abc123.jpg") instead of a directory + filename pair
     * — convenient for the one-time batch migration, which reads paths
     * straight out of the database.
     */
    public static function convertPathToWebp($fcpath, $relativePath, $quality = 82) {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $dir = dirname($relativePath);
        $filename = basename($relativePath);
        $absoluteDir = rtrim($fcpath, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);

        $newFilename = self::convertToWebp($absoluteDir, $filename, $quality);
        if ($newFilename === NULL) {
            return NULL;
        }
        return ($dir === '.' ? '' : $dir . '/') . $newFilename;
    }

    private static function _load($path, $ext) {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return @imagecreatefromjpeg($path);
            case 'png':
                return @imagecreatefrompng($path);
            case 'gif':
                return @imagecreatefromgif($path);
            case 'bmp':
                return function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : FALSE;
            default:
                return FALSE;
        }
    }
}

/* End of file ImageService.php */
/* Location: ./application/services/ImageService.php */
