<?php namespace Devitek\Core\Foundation;

use Devitek\Core\Config\YamlFileLoader;
use Illuminate\Filesystem\Filesystem;

/**
 * Class Application
 * @package Devitek\Core\Foundation
 */
class Application extends \Illuminate\Foundation\Application
{
    /**
     * @return YamlFileLoader
     */
    public function getConfigLoader()
    {
        return new YamlFileLoader(new Filesystem, $this['path'] . '/config');
    }
}