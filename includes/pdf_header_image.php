<?php

if(!function_exists('pdf_header_image_font_path')){
    function pdf_header_image_font_path(): ?string
    {
        $paths = [
            'C:/Windows/Fonts/times.ttf',
            'C:/Windows/Fonts/Times.ttf',
            '/usr/share/fonts/truetype/msttcorefonts/times.ttf',
            '/usr/share/fonts/truetype/msttcorefonts/Times_New_Roman.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSerif-Regular.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
        ];

        foreach($paths as $path){
            if(is_file($path)){
                return $path;
            }
        }

        return null;
    }
}

if(!function_exists('pdf_header_image_load')){
    function pdf_header_image_load(string $path)
    {
        $info = @getimagesize($path);

        if(!$info){
            return null;
        }

        if($info[2] === IMAGETYPE_PNG){
            return @imagecreatefrompng($path);
        }

        if($info[2] === IMAGETYPE_JPEG){
            return @imagecreatefromjpeg($path);
        }

        return null;
    }
}

if(!function_exists('pdf_header_image_center_text')){
    function pdf_header_image_center_text($canvas, string $text, int $centerX, int $baselineY, int $fontSize, int $color, ?string $fontPath): void
    {
        if($fontPath && function_exists('imagettftext') && function_exists('imagettfbbox')){
            $box = imagettfbbox($fontSize, 0, $fontPath, $text);
            $width = is_array($box) ? abs($box[2] - $box[0]) : strlen($text) * 7;
            imagettftext($canvas, $fontSize, 0, (int)round($centerX - ($width / 2)), $baselineY, $color, $fontPath, $text);
            return;
        }

        imagestring($canvas, 5, (int)round($centerX - (strlen($text) * imagefontwidth(5) / 2)), $baselineY - 12, $text, $color);
    }
}

if(!function_exists('pdf_header_image_path')){
    function pdf_header_image_path(string $province, string $city, string $barangay): ?string
    {
        if(!extension_loaded('gd')){
            return null;
        }

        $root = realpath(__DIR__ . '/..');
        $systemDir = $root ? $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'system' : false;

        if(!$systemDir || (!is_dir($systemDir) && !mkdir($systemDir, 0777, true))){
            return null;
        }

        $leftLogo = $systemDir . DIRECTORY_SEPARATOR . 'tangub_off_seal.jpg';
        $rightLogo = $systemDir . DIRECTORY_SEPARATOR . 'mis_occ_official_seal.jpg';

        if(!is_file($leftLogo) || !is_file($rightLogo)){
            return null;
        }

        $hash = md5('v5|' . $province . '|' . $city . '|' . $barangay . '|' . filemtime($leftLogo) . '|' . filemtime($rightLogo));
        $output = $systemDir . DIRECTORY_SEPARATOR . 'official_blotter_header_' . $hash . '.jpg';

        if(is_file($output)){
            return $output;
        }

        $scale = 3;
        $width = 470 * $scale;
        $height = 92 * $scale;
        $canvas = imagecreatetruecolor($width, $height);

        if(!$canvas){
            return null;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        imagefill($canvas, 0, 0, $white);

        $logoSize = 50 * $scale;
        $logoY = 20 * $scale;
        $leftImage = pdf_header_image_load($leftLogo);
        $rightImage = pdf_header_image_load($rightLogo);

        if($leftImage){
            imagecopyresampled($canvas, $leftImage, 72 * $scale, $logoY, 0, 0, $logoSize, $logoSize, imagesx($leftImage), imagesy($leftImage));
            imagedestroy($leftImage);
        }

        if($rightImage){
            imagecopyresampled($canvas, $rightImage, 348 * $scale, $logoY, 0, 0, $logoSize, $logoSize, imagesx($rightImage), imagesy($rightImage));
            imagedestroy($rightImage);
        }

        $fontPath = pdf_header_image_font_path();
        $fontSize = 12 * $scale;
        $centerX = (int)round($width / 2);
        $lineY = 18 * $scale;
        $lineGap = 14 * $scale;
        $lines = [
            'Republic of the Philippines',
            'Province of ' . ($province !== '' ? $province : '____________________'),
            'City/Municipality of ' . ($city !== '' ? $city : '____________________'),
            'Barangay ' . ($barangay !== '' ? $barangay : '____________________'),
            'Office of the Punong Barangay',
        ];

        foreach($lines as $line){
            pdf_header_image_center_text($canvas, $line, $centerX, $lineY, $fontSize, $black, $fontPath);
            $lineY += $lineGap;
        }

        imagejpeg($canvas, $output, 96);
        imagedestroy($canvas);

        return is_file($output) ? $output : null;
    }
}
?>
