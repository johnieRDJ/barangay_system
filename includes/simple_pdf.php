<?php

class SimplePdf
{
    private array $pages = [];
    private array $current = [];
    private array $images = [];
    private float $x = 72;
    private float $y = 720;
    private float $fontSize = 12;
    private float $lineHeight = 16;
    private float $pageWidth = 612;
    private float $pageHeight = 792;
    private float $margin = 72;

    public function __construct()
    {
        $this->addPage();
    }

    public function addPage(): void
    {
        if(!empty($this->current)){
            $this->pages[] = $this->current;
        }

        $this->current = [];
        $this->x = $this->margin;
        $this->y = $this->pageHeight - $this->margin;
    }

    public function setFontSize(float $size): void
    {
        $this->fontSize = $size;
        $this->lineHeight = $size + 4;
    }

    public function text(string $text, ?float $x = null, ?float $y = null, string $align = 'left'): void
    {
        $x = $x ?? $this->x;
        $y = $y ?? $this->y;
        $this->current[] = [
            'type' => 'text',
            'text' => $text,
            'x' => $x,
            'y' => $y,
            'size' => $this->fontSize,
            'align' => $align,
        ];
    }

    public function getY(): float
    {
        return $this->y;
    }

    public function setY(float $y): void
    {
        $this->y = $y;
    }

    public function image(string $path, float $x, float $y, float $width, ?float $height = null): bool
    {
        $info = @getimagesize($path);

        if(!$info || !in_array($info[2], [IMAGETYPE_JPEG], true)){
            return false;
        }

        $height = $height ?? ($width * $info[1] / $info[0]);
        $key = realpath($path) ?: $path;

        if(!isset($this->images[$key])){
            $this->images[$key] = [
                'path' => $path,
                'width' => $info[0],
                'height' => $info[1],
                'name' => 'Im' . (count($this->images) + 1),
                'object_id' => null,
            ];
        }

        $this->current[] = [
            'type' => 'image',
            'key' => $key,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];

        return true;
    }

    public function line(string $text = ''): void
    {
        $this->ensureSpace();
        $this->text($text);
        $this->y -= $this->lineHeight;
    }

    public function center(string $text): void
    {
        $this->ensureSpace();
        $this->text($text, $this->pageWidth / 2, $this->y, 'center');
        $this->y -= $this->lineHeight;
    }

    public function blank(float $height = 10): void
    {
        $this->y -= $height;
    }

    public function paragraph(string $text): void
    {
        $maxChars = 86;
        $lines = explode("\n", wordwrap(trim($text), $maxChars, "\n", true));

        foreach($lines as $line){
            $this->line($line);
        }
    }

    public function labelValue(string $label, string $value): void
    {
        $this->line($label . ': ' . ($value !== '' ? $value : str_repeat('_', 40)));
    }

    public function output(string $path): bool
    {
        if(!empty($this->current)){
            $this->pages[] = $this->current;
            $this->current = [];
        }

        $objects = [];
        $catalogId = 1;
        $pagesId = 2;
        $fontId = 3;
        $nextId = 4;

        foreach(array_keys($this->images) as $imageKey){
            $this->images[$imageKey]['object_id'] = $nextId++;
        }

        $pageIds = [];
        $contentIds = [];

        foreach($this->pages as $page){
            $pageId = $nextId++;
            $contentId = $nextId++;
            $pageIds[] = $pageId;
            $contentIds[] = $contentId;

            $stream = '';
            foreach($page as $item){
                if(($item['type'] ?? 'text') === 'image'){
                    $image = $this->images[$item['key']] ?? null;

                    if($image){
                        $stream .= sprintf(
                            "q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n",
                            $item['width'],
                            $item['height'],
                            $item['x'],
                            $item['y'],
                            $image['name']
                        );
                    }

                    continue;
                }

                $x = $item['x'];
                $text = $this->escape($item['text']);

                if($item['align'] === 'center'){
                    $estimatedWidth = strlen($item['text']) * $item['size'] * 0.23;
                    $x -= $estimatedWidth;
                }

                $stream .= sprintf("BT\n/F1 %.2F Tf\n%.2F %.2F Td\n(%s) Tj\nET\n", $item['size'], $x, $item['y'], $text);
            }

            $xObjects = '';
            foreach($this->images as $image){
                $xObjects .= '/' . $image['name'] . ' ' . $image['object_id'] . ' 0 R ';
            }
            $resourceImages = $xObjects !== '' ? " /XObject << $xObjects >>" : '';
            $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
            $objects[$pageId] = "<< /Type /Page /Parent $pagesId 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Resources << /Font << /F1 $fontId 0 R >>$resourceImages >> /Contents $contentId 0 R >>";
        }

        foreach($this->images as $image){
            $data = file_get_contents($image['path']);

            if($data !== false){
                $objects[$image['object_id']] = "<< /Type /XObject /Subtype /Image /Width {$image['width']} /Height {$image['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($data) . " >>\nstream\n" . $data . "\nendstream";
            }
        }

        $objects[$catalogId] = "<< /Type /Catalog /Pages $pagesId 0 R >>";
        $objects[$pagesId] = "<< /Type /Pages /Kids [" . implode(' ', array_map(fn($id) => "$id 0 R", $pageIds)) . "] /Count " . count($pageIds) . " >>";
        $objects[$fontId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Roman >>";
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach($objects as $id => $body){
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$body\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for($i = 1; $i <= count($objects); $i++){
            $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root $catalogId 0 R >>\nstartxref\n$xrefOffset\n%%EOF";

        return file_put_contents($path, $pdf) !== false;
    }

    private function ensureSpace(): void
    {
        if($this->y < $this->margin){
            $this->addPage();
        }
    }

    private function escape(string $text): string
    {
        $text = str_replace(["\r", "\n"], ' ', $text);
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
?>
