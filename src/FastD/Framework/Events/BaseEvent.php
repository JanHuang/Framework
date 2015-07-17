<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/30
 * Time: 上午11:18
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Framework\Events;

use FastD\Config\Config;
use FastD\Container\Container;
use FastD\Database\Database;
use FastD\Database\Driver\Driver;
use FastD\Debug\Debug;
use FastD\Logger\Logger;
use FastD\Http\RedirectResponse;
use FastD\Http\Request;
use FastD\Routing\Router;
use FastD\Storage\StorageManager;

/**
 * Class EventAbstract
 *
 * @package FastD\Framework\Controller
 */
class BaseEvent
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StorageManager
     */
    protected $storage;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Router
     */
    protected $routing;

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param       $event
     * @param       $handle
     * @param array $parameters
     * @return \FastD\Protocol\Http\Response|string
     */
    public function call($event, $handle, array $parameters = [])
    {
        if (is_string($event)) {
            $event = $this->container->get($event, [], true);
        }

        return $this->container->getProvider()->callServiceMethod($event, $handle, $parameters);
    }

    /**
     * Get custom defined helper obj.
     *
     * @param string $helper
     * @param array $parameters
     * @param bool $newInstance
     * @return mixed
     */
    public function get($helper, $parameters = array(), $newInstance = false)
    {
        if (is_string($parameters)) {
            $parameters = $this->getParameters($parameters);
        }

        return $this->container->get($helper, $parameters, $newInstance);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('kernel.request');
    }

    /**
     * get database connection driver
     *
     * @param string $connection
     * @return Driver
     */
    public function getConnection($connection = null)
    {
        if (null === $this->database) {
            $this->database = $this->get('kernel.database', [$this->getParameters('database')]);
        }

        return $this->database->getConnection($connection);
    }

    /**
     * @param $vars
     */
    public function dump($vars)
    {
        return Debug::dump($vars);
    }

    /**
     * @param $connection
     * @return \FastD\Storage\StorageInterface
     */
    public function getStorage($connection)
    {
        if (null === $this->storage) {
            $this->storage = $this->get('kernel.storage', [$this->getParameters('storage')]);
        }

        return $this->storage->getConnection($connection);
    }

    /**
     * Get custom config parameters.
     *
     * @param string $name
     * @return mixed
     */
    public function getParameters($name = null)
    {
        if (null === $this->config) {
            $this->config = $this->get('kernel.config');
        }

        return $this->config->get($name);
    }

    /**
     * @return Router
     */
    public function getRouting()
    {
        if (null === $this->routing) {
            $this->routing = $this->get('kernel.routing');
        }

        return $this->routing;
    }

    /**
     * @param       $name
     * @param array $parameters
     * @param bool  $suffix
     * @return string
     */
    public function generateUrl($name, array $parameters = array(), $suffix = false)
    {
        $url = $this->getRouting()->generateUrl($name, $parameters, $suffix);
        if ('http' !== substr($url, 0, 4)) {
            $url = ('/' === ($path = $this->getRequest()->getBaseUrl()) ? '' : $path) . $url;
            $url = str_replace('//', '/', $url);
        }
        return $this->getRequest()->getSchemeAndHttpAndHost() . $url;
    }

    /**
     * @param     $url
     * @param int $statusCode
     * @param array $headers
     * @return \FastD\Protocol\Http\RedirectResponse
     */
    public function redirect($url, $statusCode = 302, array $headers = [])
    {
        return new RedirectResponse($url, $statusCode, $headers);
    }
}