<?php

declare(strict_types=1);

namespace Pixel2to9\Converter\Block;

use DOMXPath;
use Pixel2to9\Converter\Block;

class WhaleChart extends Block
{
    /**
     * {@inheritdoc}
     *
     * @see \Pixel2to9\Converter::convert()
     */
    public function convert(DOMXPath $xpath): void
    {
        $this->renameBlockTypeHandle($xpath, 'whale_chart', 'pixel_chart');
        $this->removeDataFields($xpath, 'btWhaleChart', ['lineCap', 'scaleColor']);
        $this->renameDataTable($xpath, 'btWhaleChart', 'btPixelPieChart');
    }
}
