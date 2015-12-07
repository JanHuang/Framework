<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/12/7
 * Time: 下午9:57
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Framework\Bundle\Commands;

use FastD\Console\Command;
use FastD\Console\IO\Input;
use FastD\Console\IO\Output;
use FastD\Config\Config;
use FastD\Http\Request;

class HttpServerCommand extends Command
{
    protected $pidFile;

    protected $handles = [];

    protected $name = 'fastd-server';

    /**
     * @var Config
     */
    protected $config;

    protected $host;

    protected $port;

    protected $server;

    public function getName()
    {
        return 'http:server';
    }

    public function rename($name)
    {
        if ('Linux' == strtoupper(PHP_OS)) {
            cli_set_process_title($name);
        }
    }

    public function configure()
    {
        $this->setOption('daemonize');
    }

    public function initConfiguration($conf = null)
    {
        if (null === $conf) {
            $conf = $this->getContainer()->singleton('kernel')->getRootPath() . '/config/server.php';
        }

        if (!file_exists($conf)) {
            throw new \RuntimeException('Server is not configuration. In ' . $conf);
        }

        $this->config = new Config();

        $this->config->load($conf);

        $this->pidFile = $this->getContainer()->singleton('kernel')->getRootPath() . '/storage/run/' . $this->name . '.pid';

        if (!is_dir(dirname($this->pidFile))) {
            mkdir(dirname($this->pidFile), 0755, true);
        }

        if ($this->config->has('handles')) {
            $this->handles = $this->config->get('handles');
            $this->config->remove('handles');
        }

        if ($this->config->has('host')) {
            $this->host = $this->config->get('host');
        }

        if ($this->config->has('port')) {
            $this->port = $this->config->get('port');
        }
    }

    public function onStart(\swoole_server $server)
    {
        $this->rename($this->name . ' master');
        if ($this->pidFile) {
            file_put_contents($this->pidFile, $server->master_pid);
        }
    }

    public function onStop()
    {
        if ($this->pidFile) {
            @unlink($this->pidFile);
        }
    }

    public function onShutdown(){}

    public function onManagerStart()
    {
        $this->rename($this->name . ' manager');
    }

    public function onWorkerStart()
    {
        $this->rename($this->name . ' worker');
    }

    public function onWorkerStop(){}

    public function onRequest($request, $response)
    {
//        $request = Request::createSwooleRequestHandle($request);



        $response->end('hello fastd server.');
    }

    public function execute(Input $input, Output $output)
    {
        $this->initConfiguration();

        $server = new \swoole_http_server($this->host, $this->port);

        $server->on('start', [$this, 'onStart']);
        $server->on('shutdown', [$this, 'onStop']);

        $server->on('managerStart', [$this, 'onManagerStart']);

        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('workerStop', [$this, 'onWorkerStop']);

        $server->on('request', [$this, 'onRequest']);

        foreach ($this->handles as $event => $handle) {
            $server->on($event, $handle);
        }

        if ($input->hasParameterOption('--daemonize')) {
            $this->config->add('dzemonize', true);
        }

        $server->set($this->config->all());

        switch ($input->getParameterArgument(0)) {
            case 'start':
                $server->start();
                break;
            case 'stop':
                break;
            case 'reload':
                break;
            case 'restart':
                break;
            case 'status':
                break;
            default:

        }
    }
}