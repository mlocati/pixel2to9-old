<?php

declare(strict_types=1);

namespace Pixel2to9\Converter;

use Closure;
use DOMElement;
use DOMXPath;
use Pixel2to9\Converter;

abstract class Block implements Converter
{
    /**
     * @param \Pixel2to9\Converter\Block\TemplateConversion\Options[] $map array keys are the old custom templates
     */
    protected function convertBlockTemplateToCSSClass(DOMXPath $xpath, string $blockHandle, array $map): void
    {
        $blockElements = $xpath->query('//block[@type="' . $blockHandle . '"]');
        foreach ($blockElements as $blockElement) {
            $customTemplate = $blockElement->getAttribute('custom-template');
            if (!is_string($customTemplate) || $customTemplate === '.php' || substr($customTemplate, -4) !== '.php') {
                continue;
            }
            $customTemplate = substr($customTemplate, 0, -4);
            if (!isset($map[$customTemplate])) {
                continue;
            }
            $options = $map[$customTemplate];
            if ($options->getNewTemplate() === '') {
                $blockElement->removeAttribute('custom-template');
            } else {
                $blockElement->setAttribute('custom-template', $options->getNewTemplate());
            }
            $newCustomClasses = preg_split('/\s+/', $options->getNewCustomClasses(), -1, PREG_SPLIT_NO_EMPTY);
            if ($newCustomClasses !== []) {
                $styleElements = $xpath->query('./style', $blockElement);
                if ($styleElements->length === 0) {
                    $styleElement = $xpath->document->createElement('style');
                    $blockElement->insertBefore($styleElement, $blockElement->firstChild);
                } else {
                    $styleElement = $styleElements->item(0);
                }
                $customClassElements = $xpath->query('./customClass', $styleElement);
                if ($customClassElements->length === 0) {
                    $customClassElement = $xpath->document->createElement('customClass');
                    $styleElement->appendChild($customClassElement);
                } else {
                    $customClassElement = $customClassElements->item(0);
                }
                $oldCustomClasses = preg_split('/\s+/', $customClassElement->nodeValue ?? '', -1, PREG_SPLIT_NO_EMPTY);
                $customClassesToBeAdded = array_diff($newCustomClasses, $oldCustomClasses);
                if ($customClassesToBeAdded !== []) {
                    $customClassElement->nodeValue = implode(' ', array_merge($oldCustomClasses, $customClassesToBeAdded));
                }
            }
        }
    }

    protected function renameBlockTypeHandle(DOMXPath $xpath, string $oldHandle, string $newHandle, ?Closure $callback = null): void
    {
        $blockElements = $xpath->query('//block[@type="' . $oldHandle . '"]');
        foreach ($blockElements as $blockElement) {
            $blockElement->setAttribute('type', $newHandle);
            if ($callback !== null) {
                $callback($blockElement);
            }
        }
    }

    protected function addDataField(DOMXPath $xpath, string $tableName, string $fieldName, ?Closure $callback = null): void
    {
        $recordElements = $xpath->query('//block/data[@table="' . $tableName . '"]/record');
        foreach ($recordElements as $recordElement) {
            if ($xpath->query('./' . $fieldName, $recordElement)->length === 0) {
                $field = $xpath->document->createElement($fieldName);
                $recordElement->appendChild($field);
                if ($callback !== null) {
                    $callback($field);
                }
            }
        }
    }

    protected function removeDataField(DOMXPath $xpath, string $tableName, string $fieldName): void
    {
        $this->removeDataFields($xpath, $tableName, [$fieldName]);
    }

    protected function removeDataFields(DOMXPath $xpath, string $tableName, array $fieldNames): void
    {
        $recordElements = $xpath->query('//block/data[@table="' . $tableName . '"]/record');
        foreach ($recordElements as $recordElement) {
            $elementsToBeRemoved = [];
            foreach ($recordElement->childNodes as $childNode) {
                // Controlla se il nodo è un elemento e ha il nome desiderato
                if ($childNode instanceof DOMElement && in_array($childNode->tagName, $fieldNames, true)) {
                    $elementsToBeRemoved[] = $childNode;
                }
            }
            foreach ($elementsToBeRemoved as $elementToBeRemoved) {
                $recordElement->removeChild($elementToBeRemoved);
            }
        }
    }

    protected function renameDataTable(DOMXPath $xpath, string $oldName, string $newName, ?Closure $callback = null): void
    {
        $dataElements = $xpath->query('//block/data[@table="' . $oldName . '"]');
        foreach ($dataElements as $dataElement) {
            $dataElement->setAttribute('table', $newName);
            if ($callback !== null) {
                $callback($dataElement);
            }
        }
    }
}
