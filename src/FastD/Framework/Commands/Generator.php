<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/19
 * Time: 下午5:34
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

/**
 * Class Generator
 *
 * @package FastD\Framework\Commands
 */
class Generator extends Command
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'bundle:generate';
    }

    /**
     * @return void|$this
     */
    public function configure()
    {
        $this->setDescription('Thank for you use bundle generator tool.');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return void
     */
    public function execute(Input $input, Output $output)
    {
        try {
            $bundle = $input->getParameterArgument(0);
        } catch(\Exception $e) {
            $output->writeln('Bundle name is empty or null. Please you try again.');
            exit;
        }

        if (empty($bundle)) {
            $output->writeln('Bundle name is empty or null. Please you try again.');
            exit;
        }

        $bundle = str_replace(':', DIRECTORY_SEPARATOR, $bundle);

        $source = $this->getEnv()->getRootPath() . '/../src';

        $this->builderStructure($source, $bundle);
    }

    public function builderStructure($path, $bundle)
    {
        $bundlePath = implode(DIRECTORY_SEPARATOR, array(
            $path,
            $bundle
        ));

        foreach (array(
            'Events',
            'Repository',
            'Exceptions',
            'Commands',
            'Services',
            'Resources/views',
            'Resources/config',
        ) as $dir) {
             $directory = implode(DIRECTORY_SEPARATOR, array(
                 $bundlePath,
                 $dir
             ));

             if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
             }
        }

        $bundleArray = explode(DIRECTORY_SEPARATOR, $bundle);

        $controller = sprintf(
            $this->getControllerTemplate(),
            str_replace(DIRECTORY_SEPARATOR, '\\', $bundle),
            '/' . strtolower(end($bundleArray)),
            strtolower(str_replace(DIRECTORY_SEPARATOR, '_', $bundle)) . '_index'
        );

        $controllerFile = $bundlePath . DIRECTORY_SEPARATOR . 'Events/Index.php';

        if (!file_exists($controllerFile)) {
            file_put_contents($controllerFile, $controller);
        }

        $bootstrapName = ucfirst(end($bundleArray));

        $bootstrap = sprintf(
            $this->getBootstrapTemplate(),
            str_replace(DIRECTORY_SEPARATOR, '\\', $bundle),
            $bootstrapName
        );

        $bootstrapFile = $bundlePath . DIRECTORY_SEPARATOR . 'Boot.php';

        if (!file_exists($bootstrapFile)) {
            file_put_contents($bootstrapFile, $bootstrap);
        }

        $routes = $bundlePath . DIRECTORY_SEPARATOR . 'Resources/config/routes.php';
        if (!file_exists($routes)) {
            file_put_contents($routes, '<?php ' . PHP_EOL);
        }
    }

    public function getControllerTemplate()
    {
        return <<<CONTROLLER
<?php

namespace %s\Events;

use Kernel\Events\EventAbstract;

class Index extends EventAbstract
{
    public function indexAction()
    {
        return 'hello world';
    }
}
CONTROLLER;
    }

    public function getBootstrapTemplate()
    {
        return <<<BUNDLE
<?php

namespace %s;

use Kernel\Bundle;

class Boot extends Bundle
{

}
BUNDLE;
    }
}