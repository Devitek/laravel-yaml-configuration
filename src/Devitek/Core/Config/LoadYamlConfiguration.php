<?php namespace Devitek\Core\Config;

use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Symfony\Component\Yaml\Parser;

use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as RepositoryContract;

class LoadYamlConfiguration extends LoadConfiguration
{
    protected function getAllowedFileExtensions()
    {
        return ['php', 'yml', 'yaml'];
    }

    protected function getAllowedPathsHelper()
    {
        return ['app_path', 'base_path', 'public_path', 'storage_path'];
    }
    
	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @param  \Illuminate\Contracts\Config\Repository  $config
	 * @return void
	 */
	protected function loadConfigurationFiles(Application $app, RepositoryContract $config)
	{

		foreach ($this->getConfigurationFiles($app) as $key => $path)
		{
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
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return array
	 */
	protected function getConfigurationFiles(Application $app)
	{

		$files = [];

        foreach ($this->getAllowedFileExtensions() as $extension) {
			foreach (Finder::create()->files()->name('*.'.$extension)->in($app->configPath()) as $file)
			{
				$nesting = $this->getConfigurationNesting($file);
	
				$files[$nesting.basename($file->getRealPath(), '.'.$extension)] = $file->getRealPath();
			}
			
		}

		return $files;
	}

	/**
	 * Get the configuration file nesting path.
	 *
	 * @param  \Symfony\Component\Finder\SplFileInfo  $file
	 * @return string
	 */
	private function getConfigurationNesting(SplFileInfo $file)
	{
		$directory = dirname($file->getRealPath());

		if ($tree = trim(str_replace(config_path(), '', $directory), DIRECTORY_SEPARATOR))
		{
			$tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree).'.';
		}

		return $tree;
	}
		
	protected function parsePathsHelpers($data)
    {
        foreach ($this->getAllowedPathsHelper() as $pathHelper) {
            $data = str_replace('%' . $pathHelper . '%', $pathHelper(), $data);
        }

        return $data;
    }
    
    protected function parseYamlOrLoadFromCache($file)
    {

	    $cachefile = $app->storagePath . '/yaml-config/cache.' . md5($file) . '.php';

        if (@filemtime($cachefile) < filemtime($file)) {
            $parser  = new Parser();
            $content = null === ($yaml = $parser->parse(file_get_contents($file))) ? [] : $yaml;
            $content = $this->parsePathsHelpers($content);

            file_put_contents($cachefile, "<?php" . PHP_EOL . PHP_EOL . "return " . var_export($content, true) . ";");
        } else {
	     	
            $content = require $cachefile;
            dd($content);
        }

        return $content;
    }
} 
