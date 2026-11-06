<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Minimal QR Code Version 1-M encoder for short alphanumeric display codes.
 * It deliberately encodes kode_tampil, not the unavailable raw wallet token.
 */
class Qr_code_svg
{
    private const SIZE = 21;
    private const DATA_CODEWORDS = 16;
    private const ECC_CODEWORDS = 10;
    private const ALPHANUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    public function render($text, $quiet_zone = 4)
    {
        $matrix = $this->matrix($text);
        $quiet_zone = max(2, min(8, (int) $quiet_zone));
        $view_size = self::SIZE + ($quiet_zone * 2);
        $path = [];

        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $black) {
                if ($black) {
                    $path[] = 'M' . ($x + $quiet_zone) . ' ' . ($y + $quiet_zone) . 'h1v1h-1z';
                }
            }
        }

        $label = htmlspecialchars('QR ' . $text, ENT_QUOTES, 'UTF-8');
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $view_size . ' ' . $view_size . '" role="img" aria-label="' . $label . '" shape-rendering="crispEdges">'
            . '<rect width="100%" height="100%" fill="#fff"/>'
            . '<path d="' . implode('', $path) . '" fill="#000"/>'
            . '</svg>';
    }

    public function render_png($text, $scale = 20, $quiet_zone = 1)
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('Ekstensi GD PHP belum tersedia.');
        }
        $matrix = $this->matrix($text);
        $scale = max(2, min(40, (int) $scale));
        $quiet_zone = max(0, min(4, (int) $quiet_zone));
        $size = (self::SIZE + ($quiet_zone * 2)) * $scale;
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $isBlack) {
                if ($isBlack) {
                    imagefilledrectangle($image, ($x + $quiet_zone) * $scale, ($y + $quiet_zone) * $scale, (($x + $quiet_zone + 1) * $scale) - 1, (($y + $quiet_zone + 1) * $scale) - 1, $black);
                }
            }
        }
        ob_start();
        imagepng($image, null, 9);
        $contents = ob_get_clean();
        imagedestroy($image);
        return $contents;
    }

    public function matrix($text)
    {
        $text = strtoupper(trim((string) $text));
        if ($text === '' || strlen($text) > 20 || strspn($text, self::ALPHANUMERIC) !== strlen($text)) {
            throw new InvalidArgumentException('Kode QR harus berisi 1-20 karakter alfanumerik QR.');
        }

        $data = $this->makeDataCodewords($text);
        $codewords = array_merge($data, $this->reedSolomonRemainder($data, self::ECC_CODEWORDS));
        $modules = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));
        $functions = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));

        $this->drawFunctionPatterns($modules, $functions);
        $this->drawCodewords($modules, $functions, $codewords, 0);
        $this->drawFormatBits($modules, $functions, 0);

        return $modules;
    }

    private function makeDataCodewords($text)
    {
        $bits = [];
        $this->appendBits($bits, 0x2, 4);
        $this->appendBits($bits, strlen($text), 9);

        for ($i = 0; $i + 1 < strlen($text); $i += 2) {
            $value = (strpos(self::ALPHANUMERIC, $text[$i]) * 45)
                + strpos(self::ALPHANUMERIC, $text[$i + 1]);
            $this->appendBits($bits, $value, 11);
        }
        if (strlen($text) % 2 !== 0) {
            $this->appendBits($bits, strpos(self::ALPHANUMERIC, $text[strlen($text) - 1]), 6);
        }

        $capacity = self::DATA_CODEWORDS * 8;
        $terminator = min(4, $capacity - count($bits));
        $this->appendBits($bits, 0, $terminator);
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $bytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $value = 0;
            for ($j = 0; $j < 8; $j++) {
                $value = ($value << 1) | $bits[$i + $j];
            }
            $bytes[] = $value;
        }

        $pad = 0;
        while (count($bytes) < self::DATA_CODEWORDS) {
            $bytes[] = ($pad++ % 2 === 0) ? 0xEC : 0x11;
        }

        return $bytes;
    }

    private function appendBits(&$bits, $value, $length)
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    private function reedSolomonRemainder($data, $degree)
    {
        $generator = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($generator) + 1, 0);
            $root = $this->gfPow2($i);
            foreach ($generator as $j => $coefficient) {
                $next[$j] ^= $coefficient;
                $next[$j + 1] ^= $this->gfMultiply($coefficient, $root);
            }
            $generator = $next;
        }

        $result = array_merge($data, array_fill(0, $degree, 0));
        for ($i = 0; $i < count($data); $i++) {
            $factor = $result[$i];
            if ($factor === 0) {
                continue;
            }
            foreach ($generator as $j => $coefficient) {
                $result[$i + $j] ^= $this->gfMultiply($coefficient, $factor);
            }
        }

        return array_slice($result, -$degree);
    }

    private function gfPow2($power)
    {
        $value = 1;
        for ($i = 0; $i < $power; $i++) {
            $value <<= 1;
            if (($value & 0x100) !== 0) {
                $value ^= 0x11D;
            }
        }
        return $value;
    }

    private function gfMultiply($x, $y)
    {
        $result = 0;
        while ($y > 0) {
            if (($y & 1) !== 0) {
                $result ^= $x;
            }
            $y >>= 1;
            $x <<= 1;
            if (($x & 0x100) !== 0) {
                $x ^= 0x11D;
            }
        }
        return $result;
    }

    private function drawFunctionPatterns(&$modules, &$functions)
    {
        for ($i = 0; $i < self::SIZE; $i++) {
            $this->setFunction($modules, $functions, 6, $i, $i % 2 === 0);
            $this->setFunction($modules, $functions, $i, 6, $i % 2 === 0);
        }

        $this->drawFinder($modules, $functions, 0, 0);
        $this->drawFinder($modules, $functions, self::SIZE - 7, 0);
        $this->drawFinder($modules, $functions, 0, self::SIZE - 7);

        for ($i = 0; $i <= 5; $i++) {
            $this->setFunction($modules, $functions, 8, $i, false);
        }
        $this->setFunction($modules, $functions, 8, 7, false);
        $this->setFunction($modules, $functions, 8, 8, false);
        $this->setFunction($modules, $functions, 7, 8, false);
        for ($i = 9; $i < 15; $i++) {
            $this->setFunction($modules, $functions, 14 - $i, 8, false);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->setFunction($modules, $functions, self::SIZE - 1 - $i, 8, false);
        }
        for ($i = 8; $i < 15; $i++) {
            $this->setFunction($modules, $functions, 8, self::SIZE - 15 + $i, false);
        }
        $this->setFunction($modules, $functions, 8, self::SIZE - 8, true);
    }

    private function drawFinder(&$modules, &$functions, $left, $top)
    {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $x = $left + $dx;
                $y = $top + $dy;
                if ($x < 0 || $x >= self::SIZE || $y < 0 || $y >= self::SIZE) {
                    continue;
                }
                $black = $dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6
                    && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6
                        || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
                $this->setFunction($modules, $functions, $x, $y, $black);
            }
        }
    }

    private function drawCodewords(&$modules, $functions, $codewords, $mask)
    {
        $bit_index = 0;
        $total_bits = count($codewords) * 8;

        for ($right = self::SIZE - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }
            for ($vertical = 0; $vertical < self::SIZE; $vertical++) {
                $upward = ((($right + 1) & 2) === 0);
                $y = $upward ? self::SIZE - 1 - $vertical : $vertical;
                for ($offset = 0; $offset < 2; $offset++) {
                    $x = $right - $offset;
                    if ($functions[$y][$x]) {
                        continue;
                    }
                    $black = false;
                    if ($bit_index < $total_bits) {
                        $black = (($codewords[intdiv($bit_index, 8)] >> (7 - ($bit_index % 8))) & 1) !== 0;
                    }
                    if ($this->mask($mask, $x, $y)) {
                        $black = !$black;
                    }
                    $modules[$y][$x] = $black;
                    $bit_index++;
                }
            }
        }
    }

    private function mask($mask, $x, $y)
    {
        if ($mask !== 0) {
            throw new InvalidArgumentException('Mask QR tidak didukung.');
        }
        return (($x + $y) % 2) === 0;
    }

    private function drawFormatBits(&$modules, &$functions, $mask)
    {
        // Error correction M has format value 00.
        $data = $mask;
        $remainder = $data;
        for ($i = 0; $i < 10; $i++) {
            $remainder = ($remainder << 1) ^ ((($remainder >> 9) & 1) * 0x537);
        }
        $bits = (($data << 10) | $remainder) ^ 0x5412;

        for ($i = 0; $i <= 5; $i++) {
            $this->setFunction($modules, $functions, 8, $i, $this->bit($bits, $i));
        }
        $this->setFunction($modules, $functions, 8, 7, $this->bit($bits, 6));
        $this->setFunction($modules, $functions, 8, 8, $this->bit($bits, 7));
        $this->setFunction($modules, $functions, 7, 8, $this->bit($bits, 8));
        for ($i = 9; $i < 15; $i++) {
            $this->setFunction($modules, $functions, 14 - $i, 8, $this->bit($bits, $i));
        }
        for ($i = 0; $i < 8; $i++) {
            $this->setFunction($modules, $functions, self::SIZE - 1 - $i, 8, $this->bit($bits, $i));
        }
        for ($i = 8; $i < 15; $i++) {
            $this->setFunction($modules, $functions, 8, self::SIZE - 15 + $i, $this->bit($bits, $i));
        }
        $this->setFunction($modules, $functions, 8, self::SIZE - 8, true);
    }

    private function bit($value, $index)
    {
        return (($value >> $index) & 1) !== 0;
    }

    private function setFunction(&$modules, &$functions, $x, $y, $black)
    {
        $modules[$y][$x] = (bool) $black;
        $functions[$y][$x] = true;
    }
}
