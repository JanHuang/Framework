<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/5/9
 * Time: 下午10:51
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Standard\Commands;

use FastD\Console\Command\Command;
use FastD\Container\Container;

/**
 * Class CommandAware
 *
 * @package FastD\Standard\Commands
 */
abstract class CommandAware extends Command
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}