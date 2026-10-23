<?php

namespace App\Services;

use App\Models\Portfolio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PortfolioPowerPointService
{
    private const SLIDE_W = 12192000;
    private const SLIDE_H = 6858000;

    public function download(Portfolio $portfolio)
    {
        return $this->downloadMany(collect([$portfolio]));
    }

    public function downloadMany(Collection|array $portfolios)
    {
        if (!class_exists(ZipArchive::class)) {
            abort(500, 'Fitur PowerPoint membutuhkan PHP extension ZIP. Aktifkan extension=zip di php.ini XAMPP lalu restart Apache.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'bing0_ppt_');
        $zip = new ZipArchive();

        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Tidak dapat membuat file PowerPoint.');
        }

        $images = [];
        $slideXml = [];
        $slideRels = [];
        $slideCount = 0;

        $slides = [];
        foreach ($portfolios as $portfolio) {
            $slides = array_merge($slides, $this->buildSlides($portfolio));
        }

        foreach ($slides as $slide) {
            $slideCount++;
            $mediaRels = [];
            $xml = $this->slideXml($slide['title'] ?? '', $slide['texts'] ?? [], $slide['image'] ?? null, $slideCount, $mediaRels, $images);
            $slideXml[$slideCount] = $xml;
            $slideRels[$slideCount] = $this->slideRels($mediaRels);
        }

        foreach ($images as $media) {
            $zip->addFile($media['path'], $media['target']);
        }

        $this->addPackageFiles($zip, $slideCount, $slideXml, $slideRels);
        $zip->close();

        $filename = $portfolios->count() === 1
            ? 'portfolio-' . $this->slug($portfolios->first()->name) . '.pptx'
            : 'portfolio-bing0dev-' . now()->format('Ymd-His') . '.pptx';

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ])->deleteFileAfterSend(true);
    }

    private function buildSlides(Portfolio $p): array
    {
        $slides = [
            [
                'title' => $p->name,
                'texts' => array_filter([
                    $p->tagline,
                    $p->client_name ? 'Client: ' . $p->client_name : null,
                    $p->category ? 'Kategori: ' . $p->category : null,
                    $p->project_year ? 'Tahun: ' . $p->project_year : null,
                ]),
                'image' => $this->pptImagePath($p->banner) ?: $this->pptImagePath($p->thumbnail),
            ],
            [
                'title' => 'Ringkasan Project',
                'texts' => array_filter([
                    $p->description,
                    $p->duration ? 'Durasi pengerjaan: ' . $p->duration : null,
                    $p->url ? 'URL: ' . $p->url : null,
                ]),
            ],
        ];

        if ($p->problem || $p->solution || $p->results) {
            $texts = [];
            if ($p->problem) $texts[] = "Masalah\n" . $p->problem;
            if ($p->solution) $texts[] = "Solusi\n" . $p->solution;
            if ($p->results) $texts[] = "Hasil / Dampak\n" . $p->results;
            $slides[] = ['title' => 'Masalah, Solusi & Dampak', 'texts' => $texts];
        }

        if (!empty($p->features) || !empty($p->technologies)) {
            $texts = [];
            if (!empty($p->features)) $texts[] = "Fitur Utama\n• " . implode("\n• ", $p->features);
            if (!empty($p->technologies)) $texts[] = "Teknologi\n" . implode(' · ', $p->technologies);
            $slides[] = ['title' => 'Fitur & Teknologi', 'texts' => $texts];
        }

        foreach (array_values($p->gallery ?: []) as $i => $image) {
            if ($this->pptImagePath($image)) {
                $slides[] = [
                    'title' => 'Galeri Project ' . ($i + 1),
                    'texts' => [],
                    'image' => $image,
                ];
            }
        }

        if ($p->testimonial_text) {
            $slides[] = [
                'title' => 'Testimonial',
                'texts' => array_filter([
                    '"' . $p->testimonial_text . '"',
                    $p->testimonial_author ? '— ' . $p->testimonial_author : null,
                ]),
            ];
        }

        $slides[] = [
            'title' => 'B!ng0.dev',
            'texts' => ['Jasa pengembangan website & aplikasi custom', 'Portfolio Project: ' . $p->name],
        ];

        return $slides;
    }

    private function pptImagePath(?string $relative): ?string
    {
        if (!$relative || !$this->imageExists($relative)) {
            return null;
        }

        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png'], true) ? $relative : null;
    }

    private function imageExists(?string $relative): bool
    {
        return $relative && Storage::disk('public')->exists($relative);
    }

    private function slideXml(string $title, array $texts, ?string $image, int $slideNo, array &$rels, array &$images): string
    {
        $shapeId = 2;
        $body = '';

        $body .= $this->textShape(2, 500000, 350000, 11200000, 850000, $title, 3000, true);

        if ($image && $this->pptImagePath($image)) {
            $mediaId = count($images) + 1;
            $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $ext = $ext === 'jpeg' ? 'jpg' : $ext;
            if (!in_array($ext, ['jpg', 'png', 'gif', 'bmp'], true)) {
                $ext = 'jpg';
            }

            $mediaTarget = 'ppt/media/image' . $mediaId . '.' . $ext;
            $images[] = [
                'path' => Storage::disk('public')->path($image),
                'target' => $mediaTarget,
            ];

            $relId = 'rId' . (count($rels) + 1);
            $rels[] = ['id' => $relId, 'target' => '../media/image' . $mediaId . '.' . $ext];

            $body .= $this->pictureShape($shapeId++, $relId, 700000, 1450000, 10800000, 4800000);
        }

        $startY = $image ? 1550000 : 1450000;
        if (!$image) {
            $height = max(700000, min(4700000, count($texts) * 950000));
            $body .= $this->textShape($shapeId++, 850000, $startY, 10500000, $height, implode("\n\n", $texts), 2100, false);
        } elseif ($texts) {
            $body .= $this->textShape($shapeId++, 900000, 5050000, 10400000, 1050000, implode("\n\n", $texts), 1500, false);
        }

        return $this->slideRoot($body);
    }

    private function slideRoot(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            . 'xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $body
            . '</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sld>';
    }

    private function textShape(int $id, int $x, int $y, int $cx, int $cy, string $text, int $fontSize, bool $bold): string
    {
        $paragraphs = '';
        foreach (preg_split("/\n/", $text) as $line) {
            $paragraphs .= '<a:p><a:r><a:rPr lang="id-ID" sz="' . $fontSize . '"'
                . ($bold ? ' b="1"' : '') . '><a:solidFill><a:srgbClr val="0F172A"/></a:solidFill></a:rPr>'
                . '<a:t>' . $this->xml($line) . '</a:t></a:r></a:p>';
        }

        return '<p:sp><p:nvSpPr><p:cNvPr id="' . $id . '" name="Text ' . $id . '"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr>'
            . '<p:spPr><a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr>'
            . '<p:txBody><a:bodyPr wrap="square" rtlCol="0"/><a:lstStyle/>' . $paragraphs . '</p:txBody></p:sp>';
    }

    private function pictureShape(int $id, string $relId, int $x, int $y, int $cx, int $cy): string
    {
        return '<p:pic><p:nvPicPr><p:cNvPr id="' . $id . '" name="Portfolio image ' . $id . '"/>'
            . '<p:cNvPicPr preferRelativeResize="0"/><p:nvPr/></p:nvPicPr>'
            . '<p:blipFill><a:blip r:embed="' . $relId . '"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
            . '<p:spPr><a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic>';
    }

    private function slideRels(array $rels): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($rels as $rel) {
            $xml .= '<Relationship Id="' . $rel['id'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="' . $rel['target'] . '"/>';
        }
        $xml .= '</Relationships>';
        return $xml;
    }

    private function addPackageFiles(ZipArchive $zip, int $slideCount, array $slides, array $rels): void
    {
        $zip->addFromString('[Content_Types].xml', $this->contentTypes($slideCount, $slides));
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('ppt/presentation.xml', $this->presentationXml($slideCount));
        $zip->addFromString('ppt/_rels/presentation.xml.rels', $this->presentationRels($slideCount));
        $zip->addFromString('ppt/presProps.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:presentationPr xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"/>');
        $zip->addFromString('ppt/viewProps.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:viewPr xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"><p:normalViewPr/></p:viewPr>');
        $zip->addFromString('ppt/theme/theme1.xml', $this->themeXml());
        $zip->addFromString('ppt/slideMasters/slideMaster1.xml', $this->slideMasterXml());
        $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', $this->slideMasterRels());
        $zip->addFromString('ppt/slideLayouts/slideLayout1.xml', $this->slideLayoutXml());
        $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', $this->slideLayoutRels());

        for ($i = 1; $i <= $slideCount; $i++) {
            $zip->addFromString('ppt/slides/slide' . $i . '.xml', $slides[$i]);
            $zip->addFromString('ppt/slides/_rels/slide' . $i . '.xml.rels', $rels[$i]);
        }
    }

    private function contentTypes(int $count, array $slides): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Default Extension="jpg" ContentType="image/jpeg"/>'
            . '<Default Extension="png" ContentType="image/png"/>'
            . '<Default Extension="gif" ContentType="image/gif"/>'
            . '<Default Extension="bmp" ContentType="image/bmp"/>'
            . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/presProps.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presProps+xml"/>'
            . '<Override PartName="/ppt/viewProps.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.viewProps+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>';
        for ($i = 1; $i <= $count; $i++) {
            $xml .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }
        return $xml . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
            . '</Relationships>';
    }

    private function presentationXml(int $count): string
    {
        $ids = '';
        for ($i = 1; $i <= $count; $i++) {
            $ids .= '<p:sldId id="' . (255 + $i) . '" r:id="rId' . $i . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' saveSubsetFonts="1" autoCompressPictures="0" strictFirstAndLastChars="0">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rIdMaster"/></p:sldMasterIdLst>'
            . '<p:sldIdLst>' . $ids . '</p:sldIdLst>'
            . '<p:sldSz cx="' . self::SLIDE_W . '" cy="' . self::SLIDE_H . '" type="screen16x9"/>'
            . '<p:notesSz cx="6858000" cy="9144000"/><p:defaultTextStyle/>'
            . '</p:presentation>';
    }

    private function presentationRels(int $count): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rIdMaster" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>';

        for ($i = 1; $i <= $count; $i++) {
            $xml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide' . $i . '.xml"/>';
        }

        return $xml . '</Relationships>';
    }

    private function slideMasterXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldMaster xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld name="B!ng0 Master"><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr></p:spTree></p:cSld>'
            . '<p:clrMap accent1="3B82F6" accent2="0F172A" accent3="10B981" accent4="F59E0B" accent5="EF4444" accent6="8B5CF6" bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" hlink="4F81BD" folHlink="800080"/>'
            . '<p:sldLayoutIdLst><p:sldLayoutId id="1" r:id="rId1"/></p:sldLayoutIdLst>'
            . '<p:hf/><p:txStyles><p:titleStyle/><p:bodyStyle/><p:otherStyle/></p:txStyles></p:sldMaster>';
    }

    private function slideMasterRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="../theme/theme1.xml"/>'
            . '</Relationships>';
    }

    private function slideLayoutXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldLayout xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" type="blank" preserve="1">'
            . '<p:cSld name="Blank"><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr/></p:spTree></p:cSld>'
            . '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sldLayout>';
    }

    private function slideLayoutRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'
            . '</Relationships>';
    }

    private function themeXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="B!ng0">'
            . '<a:themeElements><a:clrScheme name="B!ng0"><a:dk1><a:sysClr val="windowText" lastClr="000000"/></a:dk1><a:lt1><a:sysClr val="window" lastClr="FFFFFF"/></a:lt1>'
            . '<a:dk2><a:srgbClr val="0F172A"/></a:dk2><a:lt2><a:srgbClr val="FFFFFF"/></a:lt2>'
            . '<a:accent1><a:srgbClr val="3B82F6"/></a:accent1><a:accent2><a:srgbClr val="0F172A"/></a:accent2><a:accent3><a:srgbClr val="10B981"/></a:accent3><a:accent4><a:srgbClr val="F59E0B"/></a:accent4><a:accent5><a:srgbClr val="EF4444"/></a:accent5><a:accent6><a:srgbClr val="8B5CF6"/></a:accent6>'
            . '<a:hlink><a:srgbClr val="0563C1"/></a:hlink><a:folHlink><a:srgbClr val="954F72"/></a:folHlink></a:clrScheme>'
            . '<a:fontScheme name="B!ng0"><a:majorFont><a:latin typeface="Aptos Display"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont><a:minorFont><a:latin typeface="Aptos"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont></a:fontScheme>'
            . '<a:fmtScheme name="B!ng0"><a:fillStyleLst/><a:lnStyleLst/><a:effectStyleLst/><a:bgFillStyleLst/></a:fmtScheme></a:themeElements></a:theme>';
    }

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim($value ?: 'project', '-');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
