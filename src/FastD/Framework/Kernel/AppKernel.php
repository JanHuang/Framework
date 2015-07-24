<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/11
 * Time: 下午3:57
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Framework\Kernel;

use FastD\Config\Config;
use FastD\Container\Container;
use FastD\Debug\Debug;
use FastD\Framework\Bundle;
use FastD\Http\Request;
use FastD\Http\Response;
use FastD\Framework\Events\BaseEvent;

/**
 * Class AppKernel
 *
 * @package FastD\Framework\Kernel
 */
abstract class AppKernel extends Terminal
{
    /**
     * The FastD application version.
     *
     * @const string
     */
    const VERSION = 'v1.0.x';

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * App containers.
     * Storage app component.
     *
     * @var array
     */
    protected $components = array(
        'kernel.template'   => 'FastD\\Template\\Template',
        'kernel.logger'     => 'FastD\\Logger\\Logger',
        'kernel.database'   => 'FastD\\Database\\Database',
        'kernel.storage'    => 'FastD\\Storage\\StorageManager',
        'kernel.request'    => 'FastD\\Http\\Request::createRequestHandle',
    );

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Bundle
     */
    private $bundles = array();

    /**
     * @var static
     */
    protected static $app;

    /**
     * Constructor. Initialize framework components.
     *
     * @param $env
     */
    public function __construct($env)
    {
        $this->environment = $env;

        $this->debug = 'prod' === $this->environment ? false : true;

        $this->components = array_merge(
            $this->registerHelpers(),
            $this->components
        );

        $this->bundles = $this->registerBundles();
    }

    /**
     * @return Bundle
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Get application running environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Bootstrap application. Loading cache,bundles,configuration,router and other.
     *
     * @return void
     */
    public function boot()
    {
        $this->initializeContainer();

        $this->initializeConfigure();

        $config = $this->container->get('kernel.config');

        Debug::enable($this->container->get('kernel.logger')->createLogger($config->get('logger.error')), $config->get('error.page'));

        unset($config);

        $this->initializeRouting();
    }

    /**
     * Initialize application container.
     *
     * @return void
     */
    public function initializeContainer()
    {
        $this->container = new Container($this->components);

        $this->container->set('kernel', $this);
    }

    /**
     * Initialize application configuration.
     *
     * @return Config
     */
    public function initializeConfigure()
    {
        $config = new Config();

        $variables = array_merge($this->registerConfigVariable(), array(
            'root.path' => $this->getRootPath(),
            'env'       => $this->getEnvironment(),
            'debug'     => $this->isDebug(),
            'version'   => AppKernel::VERSION,
            'date'      => date('Ymd'),
        ));

        $config->setVariable($variables);

        $config->load($this->getRootPath() . '/config/global.php');

        $this->registerConfiguration($config);

        foreach ($this->getBundles() as $bundle) {
            $file = $bundle->getRootPath() . '/Resources/config/config.php';
            if (file_exists($file)) {
                $config->load($file);
            }
        }

        $this->container->set('kernel.config', $config);

        unset($config);
    }

    /**
     * Loaded application routing.
     *
     * Loaded register bundle routes configuration.
     */
    public function initializeRouting()
    {
        foreach ($this->getBundles() as $bundle) {
            if (file_exists($routes = $bundle->getRootPath() . '/Resources/config/routes.php')) {
                include $routes;
            }
        }

        $this->container->set('kernel.routing', \Routes::getRouter());
    }

    /**
     * @param Request $request
     * @return \FastD\Routing\Route
     */
    public function detachRoute(Request $request)
    {
        $router = $this->container->get('kernel.routing');

        $route = $router->match($request->getPathInfo());

        $router->matchMethod($request->getMethod(), $route);

        $router->matchFormat($request->getFormat(), $route);

        return $route;
    }

    /**
     * Handle http request.
     *
     * @param Request $request
     * @return Response
     */
    public function handleHttpRequest(Request $request)
    {
        $this->container->set('kernel.request', $request);

        $route = $this->detachRoute($request);

        $callback = $route->getCallback();

        if (is_array($callback)) {
            $event = $callback[0];
            $handle = $callback[1];
        } else {
            list ($event, $handle) = explode('@', $callback);
        }

        $event = $this->container->set('callback', $event)->get('callback');
        if ($event instanceof BaseEvent) {
            $event->setContainer($this->container);
        }

        if (method_exists($event, '__initialize')) {
            $this->container->getProvider()->callServiceMethod($event, '__initialize');
        }

        $response = $this->container->getProvider()->callServiceMethod($event, $handle, $route->getParameters());

        if ($response instanceof Response) {
            return $response;
        }

        return new Response($response);
    }

    /**
     * Application running terminate. Now, The application should be exit.
     * Here application can save request log in log.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate(Request $request, Response $response)
    {
        $context = [
            'request_date'  => date('Y-m-d H:i:s', $request->getRequestTime()),
            'response_date' => date('Y-m-d H:i:s', microtime(true)),
            'ip'            => $request->getClientIp(),
            'format'        => $request->getFormat(),
            'method'        => $request->getMethod(),
            'status_code'   => $response->getStatusCode(),
            '_GET'          => $request->query->all(),
            '_POST'         => $request->request->all(),
        ];

        if (!$this->isDebug()) {
            $this
                ->container
                ->get('kernel.logger')
                ->createLogger($this->container->get('kernel.config')->get('logger.access'))
                ->addInfo($request->getPathInfo(), $context)
            ;
        } else if(false === strpos($response->header->has('Content-Type') ? $response->header->get('Content-Type') : '', 'application')) {
            $path = $request->getBaseUrl();
            if ('' != pathinfo($path, PATHINFO_EXTENSION)) {
                $path = pathinfo($path, PATHINFO_DIRNAME);
                if ('' == $path || '/' == $path) {
                    $path = '';
                }
            }
            Debug::showDebugBar($path . '/debugbar', $context);
        }

        unset($context);
    }

    /**
     * Get application work space directory.
     *
     * @return string
     */
    public function getRootPath()
    {
        if (null === $this->rootPath) {
            $this->rootPath = dirname((new \ReflectionClass($this))->getFileName());
        }

        return $this->rootPath;
    }
}