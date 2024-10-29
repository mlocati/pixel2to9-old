<?php

declare(strict_types=1);

namespace Concrete\Package\Pixel2to9;

use Concrete\Core\Database\EntityManager\Provider\ProviderInterface;
use Concrete\Core\Package\Package;

defined('C5_EXECUTE') or die('Access denied.');

class Controller extends Package implements ProviderInterface
{
    /**
     * The package handle.
     *
     * @var string
     */
    protected $pkgHandle = 'pixel2to9';

    /**
     * The package version.
     *
     * @var string
     */
    protected $pkgVersion = '0.0.1';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.0';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$phpVersionRequired
     */
    protected $phpVersionRequired = '7.4';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$pkgAutoloaderRegistries
     */
    protected $pkgAutoloaderRegistries = [
        'src' => 'Pixel2to9',
    ];

    /**
     * {@inheritdoc}
     *
     * @see Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Pixel 2to9');
    }

    /**
     * {@inheritdoc}
     *
     * @see Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('This package lets you convert Concrete CIF xml files from Pixel 2 to Pixel 9.');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Database\EntityManager\Provider\ProviderInterface::getDrivers()
     */
    public function getDrivers()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        parent::install();
        $this->installXml();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgrade()
     */
    public function upgrade()
    {
        parent::upgrade();
        $this->installXml();
    }

    /**
     * Install/update data from install XML file.
     */
    private function installXml()
    {
        $this->installContentFile('install.xml');
    }
}
