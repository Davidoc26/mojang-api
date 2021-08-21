<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Renderer;


class Renderer
{
    public static function renderHead(string $url, int $size, bool $onlyBase64 = false): string
    {
        $skin = imagecreatefrompng($url);
        $head = imagecreatetruecolor($size, $size);

        imagecopyresized($head, $skin, 0, 0, 8, 8, $size, $size, 8, 8);

        ob_start();
        imagepng($head, null, -1);
        $output = ob_get_contents();
        ob_end_clean();

        imagedestroy($skin);
        imagedestroy($head);

        if ($onlyBase64) {
            return base64_encode($output);
        }

        return "data:image/png;base64," . base64_encode($output);
    }
}
