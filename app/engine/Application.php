<?php
namespace Engine;

use Engine\Plugin\DispatchErrorHandler;
use Phalcon\Annotations\Adapter\Memory as AnnotationsMemory;
use Phalcon\Cache\Frontend\Data as CacheData;
use Phalcon\Cache\Frontend\Output as CacheOutput;
use Phalcon\Db\Adapter;
use Phalcon\Db\Profiler as DatabaseProfiler;
use Phalcon\DI;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Loader;
use Phalcon\Logger\Adapter\File;
use Phalcon\Logger;
use Phalcon\Logger\Formatter\Line as FormatterLine;
use Phalcon\Mvc\Application as PhalconApplication;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\MetaData\Strategy\Annotations as StrategyAnnotations;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\Router\Annotations as RouterAnnotations;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Registry;
use Phalcon\Session\Adapter as SessionAdapter;
use Phalcon\Session\Adapter\Files as SessionFiles;

/**
 * Application class.
 *
 * @package   Engine
 */
class Application extends PhalconApplication
{
    const
        /**
         * Default module.
         */
        SYSTEM_DEFAULT_MODULE = 'seller';

    /**
     * Application configuration.
     *
     * @var Config
     */
    protected $_config;

    /**
     * Loaders for different modes.
     *
     * @var array
     */
    private $_loaders =
        [
            'normal' => [
                'environment',
                'cache',
                'annotations',
                'database',
                'router',
                'view',
                'session',
                'flash',
                'engine'
            ]
        ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $di = new DI\FactoryDefault();
        $this->_config = config('config');
        $registry = new Registry();
        $registry->modules = [self::SYSTEM_DEFAULT_MODULE, 'seller'];

        $registry->directories = (object)[
            'engine' => APP_PATH . '/engine/',
            'modules' => APP_PATH . '/modules/',
            'plugins' => APP_PATH . '/plugins/',
            'library' => APP_PATH . '/library/'
        ];

        $di->set('registry', $registry);
        $di->setShared('config', $this->_config);
        parent::__construct($di);
    }

    /**
     * Runs the application, performing all initializations.
     *
     * @param string $mode Mode name.
     *
     * @return void
     */
    public function run($mode = 'normal')
    {
        if (empty($this->_loaders[$mode])) {
            $mode = 'normal';
        }

        // Set application main objects.
        $di = $this->_dependencyInjector;
        $di->setShared('app', $this);
        $config = $this->_config;
        $eventsManager = new EventsManager();
        $this->setEventsManager($eventsManager);

        // Init base systems first.
        $this->_initLogger($di, $config);
        $this->_initLoader($di, $config, $eventsManager);

//        $this->_attachEngineEvents($eventsManager, $config);
        // Init services and engine system.
        foreach ($this->_loaders[$mode] as $service) {
            $serviceName = ucfirst($service);
            $eventsManager->fire('init:before' . $serviceName, null);
            $result = $this->{'_init' . $serviceName}($di, $config, $eventsManager);
            $eventsManager->fire('init:after' . $serviceName, $result);
        }
        $di->setShared('eventsManager', $eventsManager);
    }

    /**
     * Init modules and register them.
     *
     * @param array $modules Modules bootstrap classes.
     * @param boolean $merge Merge with existing.
     * @return $this
     */
    public function registerModule($modules, $merge = NULL)
    {
        $bootstraps = [];
        $di = $this->getDI();

        foreach ($modules as $moduleName => $moduleClass) {
            if (isset($this->_modules[$moduleName])) {
                continue;
            }

            $bootstrap = new $moduleClass['className']($di, $this->getEventsManager());
            $bootstraps[$moduleName] = function () use ($bootstrap, $di) {
                $bootstrap->registerServices($di);

                return $bootstrap;
            };
        }
        return parent::registerModules($bootstraps, $merge);
    }

    /**
     * Get application output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->handle()->getContent();
    }


    /**
     * Check if application is used from console.
     *
     * @return bool
     */
    public function isConsole()
    {
        return (php_sapi_name() == 'cli');
    }


    /**
     * Attach required events.
     *
     * @param EventsManager $eventsManager Events manager object.
     * @param Config $config Application configuration.
     *
     * @return void
     */
    protected function _attachEngineEvents($eventsManager, $config)
    {
        // Attach modules plugins events.
        $events = (array)$config->events;
        $cache = [];
        foreach ($events as $item) {
            list ($class, $event) = explode('=', $item);
            if (isset($cache[$class])) {
                $object = $cache[$class];
            } else {
                $object = new $class();
                $cache[$class] = $object;
            }
            $eventsManager->attach($event, $object);
        }
    }

    /**
     * Init logger.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initLogger($di, $config)
    {
        if ($config->logger->enabled) {
            $di->set(
                'logger',
                function ($file = 'errors', $format = null) use ($config) {
                    $logger =  write_log($file, "");
                    $formatter = new FormatterLine(($format ? $format : $config->logger->format));
                    $logger->setFormatter($formatter);
                    return $logger;
                },
                false
            );
        }
    }

    /**
     * Init loader.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     * @param EventsManager $eventsManager Event manager.
     *
     * @return Loader
     */
    protected function _initLoader($di, $config, $eventsManager)
    {
        // Add all required namespaces and modules.
        $registry = $di->get('registry');
        $namespaces = [];
        $bootstraps = [];
        foreach ($registry->modules as $module) {
            $moduleName = ucfirst($module);
            $namespaces[$moduleName] = $registry->directories->modules . $moduleName . '/';
            $namespaces[$moduleName . '\Controllers'] = $registry->directories->modules . $moduleName . '/controller/';
            $namespaces[$moduleName . '\Models'] = $registry->directories->modules . $moduleName . '/model/';
            $bootstraps[$module] = [
                'className' => $moduleName . '\Module',
                'path' => $registry->directories->modules . $module . '/Module.php'
            ];
        }

        $dirs['engineDir'] = $registry->directories->engine;
        $dirs['pluginDir'] = $registry->directories->plugins;
        $dirs['libraryDir'] = $registry->directories->library;

        $namespaces['Engine'] = $registry->directories->engine;
        $loader = new Loader();
        $loader->registerDirs($dirs);
        $loader->registerNamespaces($namespaces);

        $loader->register();

        $this->registerModule($bootstraps);
        $di->set('loader', $loader);

        return $loader;
    }


    /**
     * Init environment.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return Url
     */
    protected function _initEnvironment($di, $config)
    {
        if (!$config->debug) {
            set_error_handler(
                function ($errorCode, $errorMessage, $errorFile, $errorLine) {
                    throw new \ErrorException($errorMessage, $errorCode, 0, $errorFile, $errorLine);
                }
            );
        } else {
            $debug = new \Phalcon\Debug();
            $debug->setUri($config->baseUrl . '/public/js/test/');
            $debug->listen();
        }

        /**
         * The URL component is used to generate all kind of urls in the
         * application
         */
        $url = new Url();
        $url->setBaseUri($config->baseUrl);
        $di->set('url', $url);
        return $url;
    }

    /**
     * Init annotations.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initAnnotations($di, $config)
    {
        $di->set(
            'annotations',
            function () use ($config) {
                if ($config->debug && !isset($config->annotations)) {
                    $annotationsAdapter = '\Phalcon\Annotations\Adapter\\' . ucfirst($config->annotations->adapter);
                    $adapter = new $annotationsAdapter($config->annotations->toArray());
                } else {
                    $adapter = new AnnotationsMemory();
                }

                return $adapter;
            },
            true
        );
    }

    /**
     * Init router.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return Router
     */
    protected function _initRouter($di, $config)
    {
        $defaultModuleName = ucfirst(Application::SYSTEM_DEFAULT_MODULE);

        $cacheData = $di->get('cacheData');
        $router = $cacheData->get('router_data');

        if ($config->debug || $router === null) {
            $saveToCache = ($router === null);

            $modules = $di->get('registry')->modules;

            $router = new RouterAnnotations(true);
            $router->removeExtraSlashes(true);
            $router->setDefaultModule(Application::SYSTEM_DEFAULT_MODULE);
            $router->setDefaultNamespace(ucfirst(Application::SYSTEM_DEFAULT_MODULE) . '\Controllers');
            $router->setDefaultController("Index");
            $router->setDefaultAction("index");
            foreach ($modules as $module) {
                $moduleName = ucfirst($module);
                $files = scandir($di->get('registry')->directories->modules . $module . '/controller');
                foreach ($files as $file) {
                    if ($file == "." || $file == ".." || strpos($file, 'Controller.php') === false
                    ) {
                        continue;
                    }
                    $controller = $moduleName . '\Controllers\\' . str_replace('Controller.php', '', $file);
                    $router->addModuleResource(strtolower($module), $controller);
                }
            }
            if ($saveToCache) {
                $cacheData->save('router_data', $router, 2592000); // 30 days cache
            }
        }

        $di->set('router', $router);
        return $router;
    }

    /**
     * Init database.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     * @param EventsManager $eventsManager Event manager.
     *
     * @return Pdo
     */
    protected function _initDatabase($di, $config, $eventsManager)
    {
        $adapter = '\Phalcon\Db\Adapter\Pdo\\' . ucfirst($config->dbMaster->adapter);
        /** @var Pdo $connMaster */

        $connMaster = new $adapter(
            [
                "host" => $config->dbMaster->host,
                "port" => $config->dbMaster->port,
                "username" => $config->dbMaster->username,
                "password" => $config->dbMaster->password,
                "dbname" => $config->dbMaster->dbname,
                "prefix" => $config->dbMaster->prefix,
                'options' => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . $config->dbMaster->charset . "'",
                    \PDO::ATTR_CASE => \PDO::CASE_LOWER,
                ]
            ]
        );
        $connSlave = new $adapter(
            [
                "host" => $config->dbSlave->host,
                "port" => $config->dbSlave->port,
                "username" => $config->dbSlave->username,
                "password" => $config->dbSlave->password,
                "dbname" => $config->dbSlave->dbname,
                "prefix" => $config->dbSlave->prefix,
                'options' => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . $config->dbSlave->charset . "'",
                    \PDO::ATTR_CASE => \PDO::CASE_LOWER,
                ]
            ]
        );

        $isDebug = $config->debug;
        $isProfiler = $config->profiler;
        if ($isDebug || $isProfiler) {
            // Attach logger & profiler.
            $logger = null;
            $profiler = null;

            if ($isDebug) {
                $logger =  write_log("database", "");
//                $logger = new File($config->logger->path . date('Ym/') . 'database_' . date('Ymd') . '.log');
            }
            if ($isProfiler) {
                $profiler = new DatabaseProfiler();
                $di->set('profiler', $profiler);
            }

            $eventsManager->attach(
                'db',
                function ($event, $connection) use ($logger, $profiler) {
                    if ($event->getType() == 'beforeQuery') {
                        $statement = $connection->getSQLStatement();
                        if ($logger) {
                            $logger->log($statement, Logger::INFO);
                        }
                        if ($profiler) {
                            $profiler->startProfile($statement);
                        }
                    }
                    if ($event->getType() == 'afterQuery') {
                        // Stop the active profile.
                        if ($profiler) {
                            $profiler->stopProfile();
                        }
                    }
                }
            );

            $connMaster->setEventsManager($eventsManager);
            $connSlave->setEventsManager($eventsManager);
        }

        $di->set('dbMaster', $connMaster);
        $di->set('dbSlave', $connSlave);
        $di->set(
            'modelsManager',
            function () use ($config, $eventsManager) {
                $modelsManager = new ModelsManager();
                $modelsManager->setEventsManager($eventsManager);
                return $modelsManager;
            },
            true
        );

        $di->set(
            'modelsMetadata',
            function () use ($config) {
                if (!$config->debug && isset($config->metadata)) {
                    $metaDataConfig = $config->metadata;
                    $metadataAdapter = '\Phalcon\Mvc\Model\Metadata\\' . ucfirst($metaDataConfig->adapter);
                    $metaData = new $metadataAdapter($config->metadata->toArray());
                } else {
                    $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
                }
                return $metaData;
            },
            true
        );

        return $connMaster;
    }

    /**
     * Init session.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return SessionAdapter
     */
    protected function _initSession($di, $config)
    {
        if (!isset($config->session)) {
            $session = new SessionFiles();
        } else {
            $adapterClass = 'Phalcon\Session\Adapter\\' . ucfirst($config->session->adapter);
            $session = new $adapterClass($config->session->toArray());
        }

        $session->start();
        $di->setShared('session', $session);
        return $session;
    }

    /**
     * Init session.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return SessionAdapter
     */
    protected function _initView($di, $config)
    {
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir($config->view->viewsDir);
        $view->registerEngines(['.php' => '\Phalcon\Mvc\View\Engine\Php']);
        $di->setShared('view', $view);

        $eventsManager = new EventsManager();
        $eventsManager->attach('dispatch:beforeException', new DispatchErrorHandler());

        $dispatcher = new \Phalcon\Mvc\Dispatcher();
        $dispatcher->setEventsManager($eventsManager);
        $di->set('dispatcher', $dispatcher);
        return $view;
    }

    /**
     * Init cache.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initCache($di, $config)
    {
        // Get the parameters.
        $cacheAdapter = '\Phalcon\Cache\Backend\\' . ucfirst($config->cache->adapter);

        $frontEndOptions = ['lifetime' => $config->cache->lifetime];
        $backEndOptions = $config->cache->toArray();
        $frontOutputCache = new CacheOutput($frontEndOptions);
        $frontDataCache = new CacheData($frontEndOptions);
        $cacheOutputAdapter = new $cacheAdapter($frontOutputCache, $backEndOptions);

        $di->set('viewCache', $cacheOutputAdapter, true);
        $di->set('cacheOutput', $cacheOutputAdapter, true);
        $cacheDataAdapter = new $cacheAdapter($frontDataCache, $backEndOptions);
        $di->set('cacheData', $cacheDataAdapter, true);
        $di->set('modelsCache', $cacheDataAdapter, true);
    }

    /**
     * Init flash messages.
     *
     * @param DI $di Dependency Injection.
     *
     * @return void
     */
    protected function _initFlash($di)
    {
        $flashData = [
            'error' => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info',
        ];

        $di->set(
            'flash',
            function () use ($flashData) {
                $flash = new FlashDirect($flashData);
                return $flash;
            }
        );

        $di->set(
            'flashSession',
            function () use ($flashData) {
                $flash = new FlashSession($flashData);
                return $flash;
            }
        );
    }

    /**
     * Init engine.
     *
     * @param DI $di Dependency Injection.
     *
     * @return void
     */
    protected function _initEngine($di)
    {
        $di->setShared(
            'transactions',
            function () {
                $manager = new TxManager();
                return $manager->setDbService("dbMaster");

            }
        );
    }
}