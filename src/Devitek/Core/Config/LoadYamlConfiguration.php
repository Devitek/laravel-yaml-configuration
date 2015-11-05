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
     * Parse
     *
     * @param $value
     *
     * @return mixed
     */
    protected function parseValues(&$value)
    {
        if (! is_string($value)) {
            return true;
        }

        preg_match_all('/%([a-zA-Z_]+)(?::(.*))?%/', $value, $matches);

        if (empty(array_shift($matches))) {
            return true;
        }

        $function = current(array_shift($matches));

        if (! function_exists($function)) {
            return true;
        }

        $args  = current(array_shift($matches));
        $value = call_user_func_array($function, explode(',', $args));

        return true;
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

            array_walk_recursive($content, [$this, 'parseValues']);

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
