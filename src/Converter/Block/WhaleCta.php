<?php

declare(strict_types=1);

namespace Pixel2to9\Converter\Block;

use DOMElement;
use DOMXPath;
use Pixel2to9\Converter\Block;
use Pixel2to9\Converter\Block\TemplateConversion\Options;

class WhaleCta extends Block
{
    /**
     * {@inheritdoc}
     *
     * @see \Pixel2to9\Converter::convert()
     */
    public function convert(DOMXPath $xpath): void
    {
        $this->convertBlockTemplateToCSSClass($xpath, 'whale_cta', [
            'pixel_btn' => Options::create()->setNewTemplate('pixel_button'),
            'pixel_btn_3d' => Options::create()->setNewTemplate('pixel_button')->setNewCustomClasses('button:style:3d'),
            'pixel_btn_simple_right' => Options::create()->setNewTemplate('pixel_button')->setNewCustomClasses('utl:text:align:end'),
        ]);
        $this->renameBlockTypeHandle($xpath, 'whale_cta', 'pixel_cta');
        $this->renameDataTable($xpath, 'btWhaleCta', 'btPixelCta');
        $this->addDataField($xpath, 'btPixelCta', 'color', static function (DOMElement $field) {
            $field->nodeValue = ''; // Set color value
        });
    }
}
