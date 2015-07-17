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

namespace FastD\Framework\Commands;

use FastD\Console\Command;
use FastD\Console\IO\Input;
use FastD\Console\IO\Output;
use FastD\Routing\Router;

/**
 * Class RouteDump
 *
 * @package FastD\Framework\Commands
 */
class RouteDump extends Command
{
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
            ->setOption('name', null)
            ->setDescription('Thank for you use routing dump tool.')
        ;
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return void
     */
    public function execute(Input $input, Output $output)
    {
        $router = \Routes::getRouter();

        $output->writeln('');

        $name = $input->getParameterArgument(0);

        if ('' == $name) {
            $this->showRouteCollections($router, $output);
        } else {
            $route = $router->getRoute($name);
            $output->write('Route [');
            $output->write('"' . $name . '"', Output::STYLE_SUCCESS);
            $output->writeln(']');
            $output->writeln("Name:\t\t" . $route->getName());
            $output->writeln("Group:\t\t" . str_replace('//', '/', $route->getGroup()));
            $output->writeln("Path:\t\t" . $route->getPath());
            $output->writeln("Method:\t\t" . implode(', ', $route->getMethods()));
            $output->writeln("Format:\t\t" . implode(', ', $route->getFormats()));
            $output->writeln("Callback:\t" . (is_callable($route->getCallback()) ? 'Closure' : $route->getCallback()));
            $output->writeln("Defaults:\t" . implode(', ', $route->getDefaults()));
            $output->writeln("Requirements:\t" . implode(', ', $route->getRequirements()));
            $output->writeln("Path-Regex:\t" . $route->getPathRegex());
        }

        $output->writeln('');
    }

    public function showRouteCollections(Router $router, Output $output)
    {
        $length = 30;
        $output->writeln("Name" . str_repeat(' ', 26) . "Method" . str_repeat(' ', 24) . "Group" . str_repeat(' ', 25) . "Path", Output::STYLE_SUCCESS);
        foreach ($router->getCollections() as $name => $route) {
            $method = implode(', ', $route->getMethods());
            $group = str_replace('//', '/', $route->getGroup());
            $group = empty($group) ? '/' : $group;
            $output->writeln(
                $route->getName() . str_repeat(' ', ($length - strlen($route->getName()))) .
                $method . str_repeat(' ', ($length - strlen($method))) .
                str_replace('//', '/', $group . str_repeat(' ', ($length - strlen($group)))) .
                str_replace('//', '/', $route->getPath())
            );
        }
    }
}