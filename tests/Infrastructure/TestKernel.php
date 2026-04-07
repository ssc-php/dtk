<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Infrastructure;

use Psr\Container\ContainerInterface;
use Ssc\Dtk\Infrastructure\Symfony\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

final readonly class TestKernel
{
    public static function make(): self
    {
        $appKernel = new AppKernel('test', false);
        $appKernel->boot();

        $container = $appKernel->getContainer();

        $application = new Application($appKernel);
        $application->setAutoExit(false);

        $stderrApplicationTester = new StderrApplicationTester($application);

        return new self(
            $appKernel,
            $stderrApplicationTester,
            $container,
        );
    }

    public function __construct(
        private AppKernel $appKernel,
        private ApplicationTester $applicationTester,
        private ContainerInterface $container,
    ) {
    }

    public function appKernel(): AppKernel
    {
        return $this->appKernel;
    }

    public function application(): ApplicationTester
    {
        return $this->applicationTester;
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
