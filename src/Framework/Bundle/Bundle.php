<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/26
 * Time: 下午11:16
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace FastD\Framework\Bundle;

use FastD\Config\Config;
use FastD\Routing\Router;
use FastD\Container\ContainerAware;

/**
 * Class Bundle
 *
 * @package FastD\Framework\Bundle
 */
class Bundle extends ContainerAware implements BundleInterface
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var
     */
    protected $shortName;

    /**
     * Constructs a ReflectionClass
     *
     * @link  http://php.net/manual/en/reflectionclass.construct.php
     * @since 5.0
     */
    public function __construct()
    {
        $reflection = new \ReflectionClass($this);

        $this->rootPath = dirname($reflection->getFileName());

        $this->namespace = $reflection->getNamespaceName();

        $this->name = $reflection->getName();

        $this->shortName = $reflection->getShortName();

        unset($reflection);
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get namespace. Alias getNamespaceName
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Register bundle routing list.
     *
     * @param Router $router
     * @param string $env
     * @return void
     */
    public function registerRouting(Router $router, $env)
    {
        include $this->getRootPath() . '/Resources/config/routes.php';
    }

    /**
     * Register bundle configuration.
     *
     * @param Config $config
     * @param string $env
     * @return void
     */
    public function registerConfiguration(Config $config, $env)
    {
        $config->load($this->getRootPath() . '/Resources/config/config.php');
    }

    /**
     * @return array
     */
    public function registerExtensions()
    {
        return [];
    }
}