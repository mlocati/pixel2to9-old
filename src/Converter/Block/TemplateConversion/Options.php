<?php

declare(strict_types=1);

namespace Pixel2to9\Converter\Block\TemplateConversion;

final class Options
{
    private string $newCustomClasses = '';

    private string $newTemplate = '';

    public static function create(): self
    {
        return new self();
    }

    public function getNewCustomClasses(): string
    {
        return $this->newCustomClasses;
    }

    public function setNewCustomClasses(string $value): self
    {
        $this->newCustomClasses = $value;

        return $this;
    }

    public function getNewTemplate(): string
    {
        return $this->newTemplate;
    }

    public function setNewTemplate(string $value): self
    {
        $this->newTemplate = $value;

        return $this;
    }
}
