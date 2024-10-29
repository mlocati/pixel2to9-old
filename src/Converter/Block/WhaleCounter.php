<?php

declare(strict_types=1);

namespace Pixel2to9\Converter\Block;

use DOMXPath;
use Pixel2to9\Converter\Block;

class WhaleCounter extends Block
{
    /**
     * {@inheritdoc}
     *
     * @see \Pixel2to9\Converter::convert()
     */
    public function convert(DOMXPath $xpath): void
    {
        $this->renameBlockTypeHandle($xpath, 'whale_counter', 'pixel_counter');
        $this->renameDataTable($xpath, 'btWhaleCounter', 'btPixelCounter');
    }
}
