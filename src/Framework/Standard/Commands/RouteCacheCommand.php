<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/8/2
 * Time: 上午12:13
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Standard\Commands;

use FastD\Console\Input\Input;
use FastD\Console\Output\Output;

/**
 * Class RouteCacheCommand
 *
 * @package FastD\Framework\Bundle\Commands
 */
class RouteCacheCommand extends CommandAware
{
    const CACHE_NAME = 'routes.cache';

    /**
     * @return string
     */
    public function getName()
    {
        return 'route:cache';
    }

    /**
     *
     */
    public function configure()
    {

    }

    public function execute(Input $input, Output $output)
    {
        $kernel = $this->getContainer()->singleton('kernel');

        $caching = $kernel->getRootPath() . DIRECTORY_SEPARATOR . RouteCacheCommand::CACHE_NAME;

        $routing = $this->getContainer()->singleton('kernel.routing');

        // Init caching file.
        file_put_contents($caching, '<?php' . PHP_EOL);

        foreach ($routing as $route) {
            $default = array() === $route->getDefaults() ? '[]' : (function () use ($route) {
                $arr = [];
                foreach ($route->getDefaults() as $name => $value) {
                    $arr[] = "'{$name}' => '$value'";
                }
                return '[' . implode(',', $arr). ']';
            })();

            $requirements = array() === $route->getRequirements() ? '[]' : (function () use ($route) {
                $arr = [];
                foreach ($route->getRequirements() as $name => $value) {
                    $arr[] = "'{$name}' => '$value'";
                }
                return '[' . implode(',', $arr). ']';
            })();

            $line = "Routes::" . strtolower($route->getMethod()) . "('{$route->getName()}', '{$route->getPath()}', '{$route->getCallback()}', {$default}, {$requirements})";

            if (null != $route->getScheme() && $route->getScheme() != ['http']) {
                $line .= '->setScheme(\'' . $route->getScheme() . '\')';
            }
            if (null != $route->getFormats()) {
                $line .= '->setFormats([\'' . implode('\',\'', $route->getFormats() ?? []) . '\'])';
            }
            file_put_contents($caching, $line . ';' . PHP_EOL, FILE_APPEND);
        }
        $output->writeln('Caching to ' . $caching . '......  <success>[OK]</success>');
        return 0;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '生成路由缓存列表';
    }
}