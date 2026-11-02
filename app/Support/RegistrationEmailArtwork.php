<?php

namespace App\Support;

use RuntimeException;

final class RegistrationEmailArtwork
{
    public static function salutation(string $participantName): string
    {
        $source = public_path('web/depth/assets/email/thank-you-salutation.png');
        $image = imagecreatefrompng($source);

        if ($image === false) {
            throw new RuntimeException('The registration email salutation artwork could not be loaded.');
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $name = trim(preg_replace('/^dr\.?\s*/i', '', $participantName));
        $textColor = imagecolorallocate($image, 36, 36, 36);
        $font = self::fontPath();

        if ($font !== null && function_exists('imagettftext')) {
            $fontSize = 16;
            while ($fontSize > 11 && self::textWidth($name, $font, $fontSize) > 520) {
                $fontSize--;
            }

            imagettftext($image, $fontSize, 0, 120, 21, $textColor, $font, $name);
        } else {
            imagestring($image, 5, 120, 5, $name, $textColor);
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        if (! is_string($png)) {
            throw new RuntimeException('The registration email salutation artwork could not be generated.');
        }

        return $png;
    }

    private static function textWidth(string $text, string $font, int $fontSize): int
    {
        $box = imagettfbbox($fontSize, 0, $font, $text);

        return $box === false ? 0 : abs($box[2] - $box[0]);
    }

    private static function fontPath(): ?string
    {
        $windows = getenv('WINDIR');
        $candidates = array_filter([
            $windows ? $windows.'/Fonts/arial.ttf' : null,
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ]);

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
