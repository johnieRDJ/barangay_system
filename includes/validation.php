<?php
if(!function_exists('barangay_allowed_suffixes')){

function barangay_allowed_suffixes(): array
{
    return ['', 'Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
}

function barangay_allowed_genders(): array
{
    return ['', 'Male', 'Female', 'Other'];
}

function barangay_allowed_civil_statuses(): array
{
    return ['', 'Single', 'Married', 'Widowed', 'Separated'];
}

function barangay_allowed_puroks(): array
{
    return ['', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
}

function barangay_max_image_upload_bytes(): int
{
    return 50 * 1024 * 1024;
}

function barangay_max_upload_label(): string
{
    return '50MB';
}

function barangay_clean_name(string $value): string
{
    return trim(preg_replace('/[^A-Za-z .\'-]/', '', $value));
}

function barangay_clean_location(string $value): string
{
    return trim(preg_replace('/[^A-Za-z .\'-]/', '', $value));
}

function barangay_clean_address(string $value): string
{
    return trim(preg_replace('/[^A-Za-z0-9 #\-\/\.,]/', '', $value));
}

function barangay_clean_phone(string $value): string
{
    return preg_replace('/\D/', '', $value);
}

function barangay_calculate_age_from_birthdate(?string $birthdate): ?int
{
    if(!$birthdate){
        return null;
    }

    $date = DateTime::createFromFormat('Y-m-d', $birthdate);
    $errors = DateTime::getLastErrors();

    if(!$date || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))){
        return null;
    }

    $today = new DateTime('today');

    if($date > $today){
        return null;
    }

    return $date->diff($today)->y;
}

function barangay_is_adult_birthdate(?string $birthdate): bool
{
    $age = barangay_calculate_age_from_birthdate($birthdate);
    return $age !== null && $age >= 18;
}

function barangay_is_ph_mobile(string $value): bool
{
    return preg_match('/^09\d{9}$/', barangay_clean_phone($value)) === 1;
}

function barangay_validate_profile_fields(string $address, string $phone, ?int $age): array
{
    $errors = [];

    if($address !== '' && !preg_match('/^[A-Za-z0-9 #\-\/\.,]+$/', $address)){
        $errors[] = 'Address can only contain letters, numbers, spaces, #, hyphen, slash, period, and comma.';
    }

    if($phone !== '' && !barangay_is_ph_mobile($phone)){
        $errors[] = 'Phone number must be an 11-digit Philippine mobile number starting with 09.';
    }

    if($age !== null && $age < 0){
        $errors[] = 'Age must be a valid number.';
    }

    return $errors;
}

function barangay_upload_image(array $file, string $folder, int $userId, array $allowedExtensions = ['jpg', 'jpeg', 'png'], int $maxBytes = 52428800): ?string
{
    if(empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE){
        return null;
    }

    if(($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || intval($file['size'] ?? 0) > $maxBytes){
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($extension, $allowedExtensions, true)){
        return null;
    }

    if(!is_dir($folder) && !mkdir($folder, 0777, true)){
        return null;
    }

    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
    $uniquePart = str_replace('.', '', uniqid('', true));
    $storedName = time() . '_' . $userId . '_' . $uniquePart . '_' . $safeName;
    $destination = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . $storedName;

    return move_uploaded_file($file['tmp_name'], $destination) ? $storedName : null;
}

function barangay_save_base64_image(string $dataUrl, string $originalName, string $folder, int $userId, int $maxBytes = 52428800): ?string
{
    if($dataUrl === '' || !preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $dataUrl, $matches)){
        return null;
    }

    $base64Data = preg_replace('/^data:image\/(jpeg|jpg|png);base64,/', '', $dataUrl);
    $binary = base64_decode($base64Data, true);

    if($binary === false || strlen($binary) === 0 || strlen($binary) > $maxBytes){
        return null;
    }

    $info = @getimagesizefromstring($binary);
    if(!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        return null;
    }

    if(!is_dir($folder) && !mkdir($folder, 0777, true)){
        return null;
    }

    $extension = $info[2] === IMAGETYPE_PNG ? 'png' : 'jpg';
    $safeBaseName = pathinfo($originalName !== '' ? $originalName : 'valid_id.' . $extension, PATHINFO_FILENAME);
    $safeBaseName = preg_replace('/[^A-Za-z0-9._-]/', '_', $safeBaseName);
    $uniquePart = str_replace('.', '', uniqid('', true));
    $storedName = time() . '_' . $userId . '_' . $uniquePart . '_' . $safeBaseName . '.' . $extension;
    $destination = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . $storedName;

    return file_put_contents($destination, $binary) !== false ? $storedName : null;
}

function barangay_clean_signature_image_file(string $sourcePath, ?string $outputPath = null): ?string
{
    if(!extension_loaded('gd')){
        return null;
    }

    $info = @getimagesize($sourcePath);
    if(!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        return null;
    }

    $image = $info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($sourcePath) : @imagecreatefromjpeg($sourcePath);
    if(!$image){
        return null;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $minX = $width;
    $minY = $height;
    $maxX = 0;
    $maxY = 0;

    imagepalettetotruecolor($image);
    imagealphablending($image, false);
    imagesavealpha($image, true);

    $sampleSize = max(2, min(14, (int) floor(min($width, $height) / 8)));
    $samples = [
        [0, 0],
        [max(0, $width - $sampleSize), 0],
        [0, max(0, $height - $sampleSize)],
        [max(0, $width - $sampleSize), max(0, $height - $sampleSize)],
    ];
    $bgR = 0;
    $bgG = 0;
    $bgB = 0;
    $bgCount = 0;

    foreach($samples as [$startX, $startY]){
        for($y = $startY; $y < min($height, $startY + $sampleSize); $y++){
            for($x = $startX; $x < min($width, $startX + $sampleSize); $x++){
                $rgba = imagecolorat($image, $x, $y);
                $bgR += ($rgba >> 16) & 0xFF;
                $bgG += ($rgba >> 8) & 0xFF;
                $bgB += $rgba & 0xFF;
                $bgCount++;
            }
        }
    }

    $bgR = $bgCount ? (int) round($bgR / $bgCount) : 255;
    $bgG = $bgCount ? (int) round($bgG / $bgCount) : 255;
    $bgB = $bgCount ? (int) round($bgB / $bgCount) : 255;
    $white = imagecolorallocatealpha($image, 255, 255, 255, 0);

    for($y = 0; $y < $height; $y++){
        for($x = 0; $x < $width; $x++){
            $rgba = imagecolorat($image, $x, $y);
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;
            $alpha = ($rgba & 0x7F000000) >> 24;
            $maxChannel = max($r, $g, $b);
            $minChannel = min($r, $g, $b);
            $saturation = $maxChannel - $minChannel;
            $brightness = (int) round(($r * 0.299) + ($g * 0.587) + ($b * 0.114));
            $bgDistance = sqrt((($r - $bgR) ** 2) + (($g - $bgG) ** 2) + (($b - $bgB) ** 2));

            $isInk = $alpha < 220 && (
                $brightness < 135 ||
                ($saturation > 50 && $brightness < 190 && $bgDistance > 60)
            );
            $isBackground = $alpha > 180 || $bgDistance < 80 || $brightness > 210 || ($saturation < 28 && $brightness > 135);

            if($isBackground){
                imagesetpixel($image, $x, $y, $white);
            }
        }
    }

    $rowInkCounts = array_fill(0, $height, 0);
    $columnInkCounts = array_fill(0, $width, 0);

    for($y = 0; $y < $height; $y++){
        for($x = 0; $x < $width; $x++){
            $rgba = imagecolorat($image, $x, $y);
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            if($r <= 245 || $g <= 245 || $b <= 245){
                $rowInkCounts[$y]++;
                $columnInkCounts[$x]++;
            }
        }
    }

    for($y = 1; $y < $height - 1; $y++){
        for($x = 1; $x < $width - 1; $x++){
            $rgba = imagecolorat($image, $x, $y);
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            if($r > 245 && $g > 245 && $b > 245){
                continue;
            }

            if($rowInkCounts[$y] < 16 || $columnInkCounts[$x] < 6){
                imagesetpixel($image, $x, $y, $white);
                continue;
            }

            $neighborInk = 0;
            for($ny = $y - 1; $ny <= $y + 1; $ny++){
                for($nx = $x - 1; $nx <= $x + 1; $nx++){
                    $neighbor = imagecolorat($image, $nx, $ny);
                    $nr = ($neighbor >> 16) & 0xFF;
                    $ng = ($neighbor >> 8) & 0xFF;
                    $nb = $neighbor & 0xFF;

                    if($nr <= 245 || $ng <= 245 || $nb <= 245){
                        $neighborInk++;
                    }
                }
            }

            if($neighborInk < 3){
                imagesetpixel($image, $x, $y, $white);
                continue;
            }

            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }
    }

    $finalRowCounts = array_fill(0, $height, 0);
    for($y = 0; $y < $height; $y++){
        for($x = 0; $x < $width; $x++){
            $rgba = imagecolorat($image, $x, $y);
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            if($r <= 245 || $g <= 245 || $b <= 245){
                $finalRowCounts[$y]++;
            }
        }
    }

    $bestStart = 0;
    $bestEnd = 0;
    $bestScore = 0;
    $currentStart = null;
    $currentEnd = null;
    $currentScore = 0;
    $gap = 0;

    for($y = 0; $y < $height; $y++){
        if($finalRowCounts[$y] >= 8){
            if($currentStart === null){
                $currentStart = $y;
            }

            $currentEnd = $y;
            $currentScore += $finalRowCounts[$y];
            $gap = 0;
        } elseif($currentStart !== null){
            $gap++;

            if($gap > 28){
                if($currentScore > $bestScore){
                    $bestStart = $currentStart;
                    $bestEnd = max($currentStart, $currentEnd);
                    $bestScore = $currentScore;
                }

                $currentStart = null;
                $currentEnd = null;
                $currentScore = 0;
                $gap = 0;
            }
        }
    }

    if($currentStart !== null && $currentScore > $bestScore){
        $bestStart = $currentStart;
        $bestEnd = max($currentStart, $currentEnd);
        $bestScore = $currentScore;
    }

    if($bestScore > 0){
        $minX = $width;
        $minY = max(0, $bestStart - 8);
        $maxX = 0;
        $maxY = min($height - 1, $bestEnd + 8);

        for($y = $minY; $y <= $maxY; $y++){
            for($x = 0; $x < $width; $x++){
                $rgba = imagecolorat($image, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if($r <= 245 || $g <= 245 || $b <= 245){
                    $minX = min($minX, $x);
                    $maxX = max($maxX, $x);
                }
            }
        }
    }

    if($maxX <= $minX || $maxY <= $minY){
        imagedestroy($image);
        return null;
    }

    $pad = 12;
    $cropX = max(0, $minX - $pad);
    $cropY = max(0, $minY - $pad);
    $cropW = min($width - $cropX, ($maxX - $minX) + ($pad * 2));
    $cropH = min($height - $cropY, ($maxY - $minY) + ($pad * 2));
    $cropped = imagecrop($image, ['x' => $cropX, 'y' => $cropY, 'width' => $cropW, 'height' => $cropH]);

    if($cropped){
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);

        $w = imagesx($cropped);
        $h = imagesy($cropped);
        for($y = 0; $y < $h; $y++){
            for($x = 0; $x < $w; $x++){
                $rgba = imagecolorat($cropped, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $alpha = ($rgba & 0x7F000000) >> 24;

                if($alpha > 200 || ($r > 240 && $g > 240 && $b > 240)){
                    imagesetpixel($cropped, $x, $y, 0xFFFFFF);
                }
            }
        }

        $processedPath = $outputPath ?: preg_replace('/\.(jpe?g|png)$/i', '_clean.jpg', $sourcePath);
        $jpeg = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($jpeg, 255, 255, 255);
        imagefill($jpeg, 0, 0, $white);
        imagecopy($jpeg, $cropped, 0, 0, 0, 0, $w, $h);

        if(imagejpeg($jpeg, $processedPath, 95)){
            imagedestroy($jpeg);
            imagedestroy($cropped);
            imagedestroy($image);
            return $processedPath;
        }

        imagedestroy($jpeg);
        imagedestroy($cropped);
    }

    imagedestroy($image);
    return null;
}

function barangay_process_signature_upload(array $file, string $folder, int $userId): ?string
{
    $storedName = barangay_upload_image($file, $folder, $userId, ['jpg', 'jpeg', 'png'], barangay_max_image_upload_bytes());
    if(!$storedName){
        return null;
    }

    $sourcePath = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . $storedName;
    $processedName = pathinfo($storedName, PATHINFO_FILENAME) . '_clean.jpg';
    $processedPath = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . $processedName;

    if(barangay_clean_signature_image_file($sourcePath, $processedPath)){
        @unlink($sourcePath);
        return $processedName;
    }

    return $storedName;
}
}
?>
