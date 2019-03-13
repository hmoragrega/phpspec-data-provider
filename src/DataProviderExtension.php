<?php

namespace HMoragrega\PhpSpec\DataProvider;

use HMoragrega\PhpSpec\DataProvider\Listener\DataProviderListener;
use HMoragrega\PhpSpec\DataProvider\Maintainer\DataProviderMaintainer;
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
        $container->define('event_dispatcher.listeners.data_provider', function () {
            return new DataProviderListener();
        }, ['event_dispatcher.listeners']);

        $container->define('runner.maintainers.data_provider', function () {
            return new DataProviderMaintainer();
        }, ['runner.maintainers']);
    }
}