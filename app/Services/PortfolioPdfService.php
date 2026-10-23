<?php

namespace App\Services;

use App\Models\Portfolio;
use Illuminate\Support\Collection;
use RuntimeException;

class PortfolioPdfService
{
    /**
     * Build a self-contained PDF without an external PDF package.
     * Images are embedded directly from storage so the generated PDF does not
     * depend on APP_URL, localhost, or a browser-accessible image URL.
     */
    public function download(Portfolio $portfolio)
    {
        return $this->downloadMany(collect([$portfolio]));
    }

    public function downloadMany(Collection|array $portfolios)
    {
        $portfolios = $portfolios instanceof Collection ? $portfolios->values() : collect($portfolios);
        if ($portfolios->isEmpty()) {
            abort(422, 'Pilih minimal satu portfolio.');
        }

        $pdf = $this->buildMany($portfolios);

        $filename = $portfolios->count() === 1
            ? preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) ($portfolios->first()->name ?: 'portfolio')))
            : 'portfolio-bing0dev-' . now()->format('Ymd-His');
        $filename = trim($filename ?: 'portfolio', '-') . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', (string) strlen($pdf))
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    private function build(Portfolio $portfolio): string
    {
        return $this->buildMany(collect([$portfolio]));
    }

    private function buildMany(Collection $portfolios): string
    {
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = 'PAGES';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $nextId = 5;
        $pageIds = [];
        $imageCache = [];

        $addImage = function (?string $path) use (&$objects, &$nextId, &$imageCache): ?array {
            if (!$path) {
                return null;
            }

            $absolute = $this->storagePath($path);
            if (!$absolute || !is_file($absolute) || !is_readable($absolute)) {
                return null;
            }

            $cacheKey = $absolute . '|' . filesize($absolute) . '|' . filemtime($absolute);
            if (isset($imageCache[$cacheKey])) {
                return $imageCache[$cacheKey];
            }

            try {
                $image = $this->prepareImage($absolute);
            } catch (\Throwable) {
                return null;
            }

            if (!$image) {
                return null;
            }

            $id = $nextId++;
            $filter = $image['filter'];
            $colorSpace = $image['colorSpace'];
            $dict = '<< /Type /XObject /Subtype /Image'
                . ' /Width ' . $image['width']
                . ' /Height ' . $image['height']
                . ' /ColorSpace ' . $colorSpace
                . ' /BitsPerComponent 8'
                . ' /Filter ' . $filter
                . ' /Length ' . strlen($image['data'])
                . ' >>';

            $objects[$id] = $dict . "\nstream\n" . $image['data'] . "\nendstream";
            $result = ['id' => $id, 'width' => $image['width'], 'height' => $image['height']];
            $imageCache[$cacheKey] = $result;

            return $result;
        };

        $addPage = function (string $content, array $images = []) use (&$objects, &$nextId, &$pageIds): void {
            $contentId = $nextId++;
            $pageId = $nextId++;

            $xobjects = [];
            foreach ($images as $name => $image) {
                $xobjects[] = '/' . $name . ' ' . $image['id'] . ' 0 R';
            }

            $resource = '/Font << /F1 3 0 R /F2 4 0 R >>';
            if ($xobjects) {
                $resource .= ' /XObject << ' . implode(' ', $xobjects) . ' >>';
            }

            $objects[$contentId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842]'
                . ' /Resources << ' . $resource . ' >>'
                . ' /Contents ' . $contentId . ' 0 R >>';

            $pageIds[] = $pageId . ' 0 R';
        };

        foreach ($portfolios as $portfolio) {
        // Cover page with the portfolio banner.
        $banner = $addImage($portfolio->banner);
        $content = "q\n";
        if ($banner) {
            [$x, $y, $w, $h] = $this->fitBox($banner['width'], $banner['height'], 40, 470, 515, 300);
            $content .= sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im1 Do Q\n", $w, $h, $x, $y);
        }
        $content .= "Q\n";
        $content .= $this->textBlock($portfolio->name, 40, 420, 24, true);
        if ($portfolio->tagline) {
            $content .= $this->textBlock($portfolio->tagline, 40, 390, 12, false, 500);
        }
        $content .= $this->textBlock('B!ng0.dev — PORTFOLIO', 40, 55, 10, true);
        $content .= $this->textBlock('Dokumen dibuat otomatis dari sistem portfolio.', 40, 38, 8, false);
        $addPage($content, $banner ? ['Im1' => $banner] : []);

        // Main project information and text content.
        $lines = [];
        $push = function (string $heading, ?string $text = null) use (&$lines): void {
            $lines[] = ['heading', $heading];
            if ($text !== null && trim($text) !== '') {
                foreach ($this->wrap($text, 92) as $line) {
                    $lines[] = ['text', $line];
                }
            }
            $lines[] = ['gap', ''];
        };

        $push('INFORMASI PROJECT');
        $push('Kategori', $portfolio->category ?: '-');
        $push('Client', $portfolio->client_name ?: '-');
        $push('Tahun', $portfolio->project_year ?: '-');
        $push('Durasi', $portfolio->duration ?: '-');
        $push('DESKRIPSI', $portfolio->description ?: '-');

        if ($portfolio->problem) $push('MASALAH', $portfolio->problem);
        if ($portfolio->solution) $push('SOLUSI', $portfolio->solution);
        if ($portfolio->results) $push('HASIL / DAMPAK', $portfolio->results);

        if ($portfolio->features && count($portfolio->features)) {
            $push('FITUR', implode("\n", array_map(fn ($v) => '- ' . $v, $portfolio->features)));
        }
        if ($portfolio->technologies && count($portfolio->technologies)) {
            $push('TEKNOLOGI', implode("\n", array_map(fn ($v) => '- ' . $v, $portfolio->technologies)));
        }
        if ($portfolio->testimonial_text) {
            $testimonial = '"' . $portfolio->testimonial_text . '"';
            if ($portfolio->testimonial_author) $testimonial .= "\n— " . $portfolio->testimonial_author;
            $push('TESTIMONIAL', $testimonial);
        }
        if ($portfolio->url) $push('URL PROJECT', $portfolio->url);

        $y = 790;
        $content = '';
        foreach ($lines as [$type, $value]) {
            if ($type === 'gap') {
                $y -= 10;
                continue;
            }

            $size = $type === 'heading' ? 11 : 10;
            $bold = $type === 'heading';
            $wrapped = $type === 'heading' ? [$value] : $this->wrap($value, 92);

            foreach ($wrapped as $line) {
                if ($y < 55) {
                    $content .= $this->textBlock('B!ng0.dev — PORTFOLIO', 40, 35, 8, true);
                    $addPage($content);
                    $content = '';
                    $y = 790;
                }
                $content .= $this->textBlock($line, 40, $y, $size, $bold);
                $y -= $type === 'heading' ? 18 : 15;
            }
        }
        $content .= $this->textBlock('B!ng0.dev — PORTFOLIO', 40, 35, 8, true);
        $addPage($content);

        // Gallery pages: all uploaded gallery images are embedded into the PDF.
        $gallery = is_array($portfolio->gallery) ? array_values(array_filter($portfolio->gallery)) : [];
        $galleryItems = [];
        foreach ($gallery as $path) {
            $img = $addImage($path);
            if ($img) $galleryItems[] = $img;
        }

        if ($galleryItems) {
            foreach (array_chunk($galleryItems, 2) as $chunk) {
                $content = $this->textBlock('GALERI PROJECT', 40, 800, 14, true);
                $images = [];
                $slots = [[40, 410, 515, 350], [40, 55, 515, 300]];

                foreach ($chunk as $i => $img) {
                    $name = 'Im' . ($i + 2);
                    [$x, $y, $w, $h] = $this->fitBox($img['width'], $img['height'], ...$slots[$i]);
                    $content .= sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /%s Do Q\n", $w, $h, $x, $y, $name);
                    $images[$name] = $img;
                }

                $content .= $this->textBlock('B!ng0.dev — PORTFOLIO', 40, 25, 8, true);
                $addPage($content, $images);
            }
        }

        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageIds) . '] /Count ' . count($pageIds) . ' >>';

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        $maxId = max(array_keys($objects));

        for ($i = 1; $i <= $maxId; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . ($objects[$i] ?? '') . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }

    private function textBlock(string $text, float $x, float $y, int $size = 10, bool $bold = false, int $maxChars = 92): string
    {
        $text = $this->clean($text);
        $text = $this->wrap($text, $maxChars)[0] ?? '';
        $font = $bold ? '/F2' : '/F1';
        return sprintf("BT\n%s %d Tf\n%.2f %.2f Td\n(%s) Tj\nET\n", $font, $size, $x, $y, $this->pdfEscape($text));
    }

    private function wrap(string $text, int $width): array
    {
        $text = $this->clean($text);
        $lines = [];
        foreach (explode("\n", $text) as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }
            $lines = array_merge($lines, explode("\n", wordwrap($paragraph, $width, "\n", true)));
        }
        return $lines ?: [''];
    }

    private function clean(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: preg_replace('/[^\x20-\x7E\n]/', '', $text);
        return trim($text);
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function storagePath(string $path): ?string
    {
        $root = realpath(storage_path('app/public'));
        if (!$root) return null;

        $path = ltrim(str_replace(['\\', '../'], ['/', ''], $path), '/');
        $absolute = realpath($root . DIRECTORY_SEPARATOR . $path);
        if (!$absolute) return null;

        return str_starts_with($absolute, $root . DIRECTORY_SEPARATOR) ? $absolute : null;
    }

    private function fitBox(int $imageWidth, int $imageHeight, float $x, float $y, float $maxWidth, float $maxHeight): array
    {
        $scale = min($maxWidth / max(1, $imageWidth), $maxHeight / max(1, $imageHeight));
        $width = $imageWidth * $scale;
        $height = $imageHeight * $scale;
        $left = $x + (($maxWidth - $width) / 2);
        $bottom = $y + (($maxHeight - $height) / 2);
        return [$left, $bottom, $width, $height];
    }

    private function prepareImage(string $path): ?array
    {
        $info = @getimagesize($path);
        if (!$info || empty($info[0]) || empty($info[1])) return null;

        if (($info[2] ?? null) === IMAGETYPE_JPEG) {
            $data = file_get_contents($path);
            if ($data === false) return null;
            return [
                'width' => (int) $info[0],
                'height' => (int) $info[1],
                'colorSpace' => '/DeviceRGB',
                'filter' => '/DCTDecode',
                'data' => $data,
            ];
        }

        if (($info[2] ?? null) === IMAGETYPE_PNG) {
            return $this->preparePng($path);
        }

        // Other formats are intentionally skipped so PDF generation stays
        // dependency-free. JPG and PNG cover the normal portfolio uploads.
        return null;
    }

    /**
     * Convert common 8-bit PNG files into PDF-compatible RGB + Flate data.
     * Supports color types 0, 2, 3, 4 and 6 with non-interlaced images.
     */
    private function preparePng(string $path): ?array
    {
        $raw = file_get_contents($path);
        if ($raw === false || substr($raw, 0, 8) !== "\x89PNG\r\n\x1a\n") return null;

        $offset = 8;
        $width = $height = $bitDepth = $colorType = $interlace = null;
        $idat = '';
        $palette = '';

        while ($offset + 8 <= strlen($raw)) {
            $length = unpack('N', substr($raw, $offset, 4))[1];
            $type = substr($raw, $offset + 4, 4);
            $data = substr($raw, $offset + 8, $length);
            $offset += 12 + $length;

            if ($type === 'IHDR') {
                $h = unpack('Nwidth/Nheight/CbitDepth/CcolorType/Ccompression/Cfilter/Cinterlace', $data);
                $width = (int) $h['width'];
                $height = (int) $h['height'];
                $bitDepth = (int) $h['bitDepth'];
                $colorType = (int) $h['colorType'];
                $interlace = (int) $h['interlace'];
            } elseif ($type === 'PLTE') {
                $palette = $data;
            } elseif ($type === 'IDAT') {
                $idat .= $data;
            } elseif ($type === 'IEND') {
                break;
            }
        }

        if (!$width || !$height || $bitDepth !== 8 || $interlace !== 0 || $idat === '') {
            return null;
        }

        $channels = match ($colorType) {
            0 => 1,
            2 => 3,
            3 => 1,
            4 => 2,
            6 => 4,
            default => 0,
        };
        if (!$channels) return null;

        $decoded = @gzuncompress($idat);
        if ($decoded === false) return null;

        $bytesPerPixel = $channels;
        $stride = $width * $channels;
        $rows = [];
        $pos = 0;
        $previous = array_fill(0, $stride, 0);

        for ($row = 0; $row < $height; $row++) {
            if ($pos >= strlen($decoded)) return null;
            $filter = ord($decoded[$pos++]);
            $scan = array_values(unpack('C*', substr($decoded, $pos, $stride)) ?: []);
            $pos += $stride;
            if (count($scan) !== $stride) return null;

            for ($i = 0; $i < $stride; $i++) {
                $left = $i >= $bytesPerPixel ? $scan[$i - $bytesPerPixel] : 0;
                $up = $previous[$i] ?? 0;
                $upLeft = $i >= $bytesPerPixel ? ($previous[$i - $bytesPerPixel] ?? 0) : 0;

                $scan[$i] = match ($filter) {
                    0 => $scan[$i],
                    1 => ($scan[$i] + $left) & 255,
                    2 => ($scan[$i] + $up) & 255,
                    3 => ($scan[$i] + intdiv($left + $up, 2)) & 255,
                    4 => ($scan[$i] + $this->paeth($left, $up, $upLeft)) & 255,
                    default => throw new RuntimeException('Unsupported PNG filter'),
                };
            }

            $rgb = '';
            for ($x = 0; $x < $width; $x++) {
                $i = $x * $channels;
                if ($colorType === 0) {
                    $v = chr($scan[$i]);
                    $rgb .= $v . $v . $v;
                } elseif ($colorType === 2) {
                    $rgb .= chr($scan[$i]) . chr($scan[$i + 1]) . chr($scan[$i + 2]);
                } elseif ($colorType === 3) {
                    $index = $scan[$i];
                    $rgb .= substr($palette, $index * 3, 3);
                } elseif ($colorType === 4) {
                    $v = chr($scan[$i]);
                    $rgb .= $v . $v . $v;
                } elseif ($colorType === 6) {
                    // Ignore alpha for PDF compatibility; use the RGB channels.
                    $rgb .= chr($scan[$i]) . chr($scan[$i + 1]) . chr($scan[$i + 2]);
                }
            }

            $rows[] = $rgb;
            $previous = $scan;
        }

        $rgbData = implode('', $rows);
        return [
            'width' => $width,
            'height' => $height,
            'colorSpace' => '/DeviceRGB',
            'filter' => '/FlateDecode',
            'data' => gzcompress($rgbData, 6),
        ];
    }

    private function paeth(int $a, int $b, int $c): int
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);
        return $pa <= $pb && $pa <= $pc ? $a : ($pb <= $pc ? $b : $c);
    }
}
