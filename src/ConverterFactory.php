<?php

declare(strict_types=1);

namespace Pixel2to9;

use Concrete\Core\Application\Application;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;

class ConverterFactory
{
    private Application $app;
    
    private ?array $converters = null;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return \Pixel2to9\Converter[]
     */
    public function getConverters(): array
    {
        return $this->converters ??= $this->loadConverters();
    }

    /**
     * @return \Pixel2to9\Converter[]
     */
    private function loadConverters(): array
    {
        $result = [];
        $fs = $this->app->make(Filesystem::class);
        $basePath = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__) . '/Converter/';
        $baseNamespace = __NAMESPACE__ . '\\Converter\\';
        foreach ($fs->allFiles(__DIR__ . '/Converter') as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $className = $baseNamespace . str_replace('/', '\\', substr(substr($file->getPathname(), strlen($basePath)), 0, -4));
            if (!class_exists($className)) {
                continue;
            }
            $class = new ReflectionClass($className);
            if ($class->isAbstract() || !$class->implementsInterface(Converter::class)) {
                continue;
            }
            $result[] = $this->app->make($className);
        }

        return $result;
    }
}
