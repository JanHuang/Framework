<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/4/29
 * Time: 下午7:35
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

$loader = include __DIR__ . '/vendor/autoload.php';

$loader->addPsr4("", [__DIR__ . '/app/src/Bundles']);

if (!class_exists('\\Application')) {
    include __DIR__ . '/app/application.php';
}
