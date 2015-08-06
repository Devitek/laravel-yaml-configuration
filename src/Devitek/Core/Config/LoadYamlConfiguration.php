<?php

namespace Devitek\Core\Config;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

class LoadYamlConfiguration extends LoadConfiguration
{
    /**
     * Returns allowed config's file extensions
     *
     * @return array
     */
    protected function getAllowedFileExtensions()
    {
        return ['php', 'yml', 'yaml'];
    }

    /**
     * Retutnrs allowed paths helpers
     *
     * @return array
     */
    protected function getAllowedPathsHelper()
    {
        return ['app_path', 'base_path', 'public_path', 'storage_path'];
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param  Application $app
     * @param  Repository  $config
     *
     * @return void
     */
    protected function loadConfigurationFiles(Application $app, Repository $config)
    {
        foreach ($this->getConfigurationFiles($app) as $key => $path) {
            $ext = substr($path, strrpos($path, '.') + 1);

            switch ($ext) {
                case 'php':
                    $config->set($key, require $path);

                    break;
                case 'yml':
                case 'yaml':
                    $config->set($key, $this->parseYamlOrLoadFromCache($path));
                    break;
            }
        }
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param  Application $app
     *
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = [];

        foreach ($this->getAllowedFileExtensions() as $extension) {
            foreach (Finder::create()->files()->name('*.' . $extension)->in($app->configPath()) as $file) {
                $nesting = $this->getConfigurationNesting($file);

                $files[$nesting . basename($file->getRealPath(), '.' . $extension)] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    protected function getConfigurationNesting(SplFileInfo $file)
    {
        $directory = dirname($file->getRealPath());

        if ($tree = trim(str_replace(config_path(), '', $directory), DIRECTORY_SEPARATOR)) {
            $tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree) . '.';
        }

        return $tree;
    }

    /**
     * Parse and replace paths
     *
     * @param $data
     *
     * @return mixed
     */
    protected function parsePathsHelpers($data)
    {
        foreach ($this->getAllowedPathsHelper() as $pathHelper) {
            $data = str_replace('%' . $pathHelper . '%', $pathHelper(), $data);
        }

        return $data;
    }

    /**
     * Parse a yaml file or load it from the cache
     *
     * @param $file
     *
     * @return array|mixed
     */
    protected function parseYamlOrLoadFromCache($file)
    {
        $cachedir  = sprintf('%s/framework/cache/yaml-configuration/', storage_path());
        $cachefile = $cachedir . 'cache.' . md5($file) . '.php';

        if (@filemtime($cachefile) < filemtime($file)) {
            $parser  = new Parser();
            $content = null === ($yaml = $parser->parse(file_get_contents($file))) ? [] : $yaml;
            $content = $this->parsePathsHelpers($content);

            if (! file_exists($cachedir)) {
                @mkdir($cachedir, 0755);
            }

            file_put_contents($cachefile, "<?php" . PHP_EOL . PHP_EOL . "return " . var_export($content, true) . ";");
        } else {
            $content = require $cachefile;
        }

        return $content;
    }
} 
