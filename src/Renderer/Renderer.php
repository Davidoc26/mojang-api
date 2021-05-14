<?php
declare(strict_types=1);


namespace MojangAPI\Renderer;


class Renderer
{
    public static function renderHead(string $url, int $size): string
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

        return "data:image/png;base64," . base64_encode($output);
    }
}