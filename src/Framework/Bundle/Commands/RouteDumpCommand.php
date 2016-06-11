<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/18
 * Time: 下午4:32
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Framework\Bundle\Commands;

use FastD\Console\Input\Input;
use FastD\Console\Input\InputOption;
use FastD\Console\Output\Output;
use FastD\Routing\Route;
use FastD\Routing\Router;

/**
 * Class RouteDump
 *
 * @package FastD\Framework\Commands
 */
class RouteDumpCommand extends CommandAware
{
    const STYLE_LIST = 1;
    const STYLE_DETAIL = 2;

    /**
     * @return string
     */
    public function getName()
    {
        return 'route:dump';
    }

    /**
     * @return void|$this
     */
    public function configure()
    {
        $this
            ->setOption('bundle')
            ->setOption('detail', null, InputOption::VALUE_NONE)
            ->setArgument('route')
        ;
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int
     */
    public function execute(Input $input, Output $output)
    {
        $router = \Routes::getRouter();

        $output->writeln('');

        $name = $input->getArgument('route');
        $bundle = $input->getOption('bundle');
        $style = $input->getOption('detail');

        if (false !== strpos($bundle, ':')) {
            $bundle = str_replace(':', '\\', $bundle);
        }

        if (null === $name) {
            $this->showRouteCollections($router, $output, $bundle, $style ? self::STYLE_DETAIL: self::STYLE_LIST);
        } else {
            $route = $router->getRoute($name);
            $this->formatOutput($route, $output, self::STYLE_DETAIL);
        }

        return 0;
    }

    /**
     * @param Router $router
     * @param Output $output
     * @param null   $bundleName
     * @return int
     */
    public function showRouteCollections(Router $router, Output $output, $bundleName = null, $style = self::STYLE_DETAIL)
    {
        $allRoutes = [];

        $bundles = $this->getContainer()->get('kernel')->getBundles();
        foreach ($bundles as $bundle) {
            foreach ($router as $name => $route) {
                $callback = $route->getCallback();
                $namespace = substr($callback, 0, strpos($callback, '\\'));
                if (0 === strpos($bundle->getNamespace(), $namespace)) {
                    $allRoutes[$bundle->getName()][] = $route;
                }
            }
        }

        if ($style === self::STYLE_LIST) {
            $output->write('Name' . str_repeat(' ', 25 - strlen('name')));
            $output->write('Method' . str_repeat(' ', 15 - strlen('Method')));
            $output->write('Scheme' . str_repeat(' ', 15 - strlen('Schema')));
            $output->writeln('Path');
        }

        if (null === $bundleName) {
            foreach ($allRoutes as $name => $routes) {
                $output->writeln('<success>Bundle: </success>' . $name);
                foreach ($routes as $route) {
                    $this->formatOutput($route, $output, $style);
                }
            }
            return 0;
        }

        foreach ($allRoutes as $name => $routes) {
            if ($name == $bundleName) {
                $output->writeln('<success>Bundle: </success>' . $name);
                foreach ($routes as $route) {
                    $this->formatOutput($route, $output, $style);
                }
            }
        }
        return 0;
    }

    /**
     * Format route output to command line.
     *
     * @param Route  $route
     * @param Output $output
     * @param        $type
     */
    public function formatOutput(Route $route, Output $output, $type = self::STYLE_DETAIL)
    {
        switch ($type) {
            case self::STYLE_DETAIL:
                $output->write('Route [');
                $output->write('<success>"' . $route->getName() . '"</success>');
                $output->writeln(']');
                $output->writeln("Path:\t\t" . str_replace('//', '/', $route->getPath()));
                $output->writeln("Method:\t\t" . $route->getMethod());
                $output->writeln("Format:\t\t" . implode(', ', $route->getFormats()));
                $output->writeln("Callback:\t" . (is_callable($route->getCallback()) ? 'Closure' : $route->getCallback()));
                $output->writeln("Defaults:\t" . implode(', ', $route->getDefaults()));
                $output->writeln("Requirements:\t" . implode(', ', $route->getRequirements()));
                $output->writeln("Path-Regex:\t" . $route->getPathRegex());
                $output->writeln('');
                break;
            case self::STYLE_LIST:
            default:
                $name = $route->getName();
                $method = $route->getMethod();
                $schema = $route->getScheme() ?? 'http';
                $path = $route->getPath();
                $output->write($name . str_repeat(' ', 25 - strlen($name)));
                $output->write($method . str_repeat(' ', 15 - strlen($method)));
                $output->write($schema . str_repeat(' ', 15 - strlen($schema)));
                $output->writeln($path);
        }

        return;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        // TODO: Implement getHelp() method.
    }
}