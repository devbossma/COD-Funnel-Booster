<?php
/**
 * CFB_Caontainer class For the a Dependency Ijection purpose,
 *  we will use 'League\Container' package for this COD Funnel Booster Plugin's Version '1.0.0'.
 *
 * @package CODFunnelBooster
 */

namespace DevBossMa\CODFunnelBooster\Core;

use League\Container\Container;
use DevBossMa\CODFunnelBooster\Core\Logging\CFBFileLogger;

use DevBossMa\CODFunnelBooster\Core\Contracts\CFBLoggerInterface;
use DevBossMa\CODFunnelBooster\Core\Services\Providers\GeoServiceProvider;
use League\Container\ReflectionContainer;
use DevBossMa\CODFunnelBooster\Managers\Setup_Wizard;
use DevBossMa\CODFunnelBooster\Managers\Dependency_Manager;
use DevBossMa\CODFunnelBooster\Managers\Config_Manager;
use DevBossMa\CODFunnelBooster\Interfaces\Plugin_Dependency_Api_Interface;
use DevBossMa\CODFunnelBooster\Services\Dependency\Dependency_Checker_Service;
use DevBossMa\CODFunnelBooster\Services\Dependency\Plugin_Dependency_Api_Service_V1;
use DevBossMa\CODFunnelBooster\Core\Contracts\GeoServiceInterface;
use DevBossMa\CODFunnelBooster\Core\Services\Geography\WooCommerceGeoService;

/**
 * CFB_Caontainer class
 */
class CFB_Container {

	/**
	 * CFB_Container instance.
	 *
	 * @var CFB_Container.
	 */
	private static $instance;

	/**
	 * This function will return the builded \League\Container\Container instance.
	 *
	 * @return \League\Container\Container
	 */
	public static function init(): Container {
		if ( null === self::$instance ) {
			self::$instance = self::build_container();
		}
		return self::$instance;
	}


	/**
	 * This function will bouild and return a \League\Container\Container instance.
	 *
	 * @return Container
	 */
	private static function build_container(): Container {
		$container = new Container();

		$container->delegate(
			new ReflectionContainer( true )
		);

		// Add Logger binding.
		$container->add( CFBLoggerInterface::class, CFBFileLogger::class )
			->setShared( true );

		// Register the Plugin API Interface with its implementation.
		$container->addShared( Plugin_Dependency_Api_Interface::class, Plugin_Dependency_Api_Service_V1::class );

		// Register singletons with explicit dependency resolution.
		$container->add(
			Dependency_Checker_Service::class,
			function () use ( $container ) {
				return new Dependency_Checker_Service(
					$container->get( Plugin_Dependency_Api_Interface::class )
				);
			}
		)->setShared( true );

		$container->add(
			Dependency_Manager::class,
			function () use ( $container ) {
				return new Dependency_Manager(
					$container->get( Plugin_Dependency_Api_Interface::class ),
					$container->get( Dependency_Checker_Service::class )
				);
			}
		)->setShared( true );

		// Update GeoService binding to use CFBLoggerInterface.
		$container->add( GeoServiceInterface::class, WooCommerceGeoService::class )
			->addArgument( CFBLoggerInterface::class )
			->setShared( true );

		// Add GeoServiceProvider binding.
		$container->add( GeoServiceProvider::class )
			->addArgument( GeoServiceInterface::class )
			->setShared( true );

		// add the Cnfig_Manager binding.
		$container->add(
			Config_Manager::class,
			function () use ( $container ) {
				return new Config_Manager(
					$container->get( GeoServiceProvider::class )
				);
			}
		)->setShared( true );

		// Add Setup_Wizard binding.
		$container->add(
			Setup_Wizard::class,
			function () use ( $container ) {
				return new Setup_Wizard(
					$container->get( Dependency_Manager::class ),
					$container->get( Config_Manager::class )
				);
			}
		)->setShared( true );

		return $container;
	}

	/**
	 * This static function will resolve the dependency injection  managed by
	 * \League\Container\Container singletone instance.
	 *
	 * @param string $_abstract The ordred Abstract class.
	 * @return object
	 */
	public static function resolve( string $_abstract ): object {
		return self::init()->get( $_abstract );
	}
}
