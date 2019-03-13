<?php declare(strict_types=1);

namespace HMoragrega\PhpSpec\DataProvider;

use HMoragrega\PhpSpec\DataProvider\Listener\DataProviderListener;
use HMoragrega\PhpSpec\DataProvider\Maintainer\DataProviderMaintainer;
use HMoragrega\PhpSpec\DataProvider\Parser\DataProviderExtractor;
use HMoragrega\PhpSpec\DataProvider\Parser\ExampleParser;
use PhpSpec\Extension;
use PhpSpec\ServiceContainer;

class DataProviderExtension implements Extension
{
    /**
     * @param ServiceContainer $container
     *
     * @param array $params
     */
    public function load(ServiceContainer $container, array $params)
    {
        $container->define('event_dispatcher.listeners.data_provider', function () use ($container) {
            return new DataProviderListener(new ExampleParser(), new DataProviderExtractor());
        }, ['event_dispatcher.listeners']);

        $container->define('runner.maintainers.data_provider', function () use ($container) {
            return new DataProviderMaintainer(new ExampleParser());
        }, ['runner.maintainers']);
    }
}