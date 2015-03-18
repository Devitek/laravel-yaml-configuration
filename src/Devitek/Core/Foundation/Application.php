<?php namespace Devitek\Core\Foundation;

use Devitek\Core\Config\YamlFileLoader;

/**
 * Class Application
 * @package Devitek\Core\Foundation
 */
class Application extends \Illuminate\Foundation\Application
{
	
    /**
     *	replace LoadConfiguration with our own version 
     */
     
 	public function bootstrapWith(array $bootstrappers)
	{
	
		$key = array_search('Illuminate\Foundation\Bootstrap\LoadConfiguration', $bootstrappers); 
		$bootstrappers[$key] = 'Devitek\Core\Config\LoadYamlConfiguration'; 

		parent::bootstrapWith($bootstrappers);
	 
	}
	
	
}