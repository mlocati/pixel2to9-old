<?php

declare(strict_types=1);

namespace Pixel2to9;

use DOMXPath;

interface Converter
{
    public function convert(DOMXPath $xpath): void;
}
