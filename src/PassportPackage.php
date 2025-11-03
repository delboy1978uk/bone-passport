<?php

declare(strict_types=1);

namespace Bone\Passport;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Contracts\Container\DependentPackagesProviderInterface;
use Bone\Contracts\Container\FixtureProviderInterface;
use Bone\Passport\Command\PassportCommand;
use Bone\Passport\Command\RoleCommand;
use Bone\Passport\Fixtures\LoadRoles;
use Bone\Passport\Middleware\PassportControlMiddleware;
use Del\Passport\PassportControl;
use Doctrine\ORM\EntityManagerInterface;

class PassportPackage implements RegistrationInterface, CommandRegistrationInterface, FixtureProviderInterface, DependentPackagesProviderInterface
{
    public function addToContainer(Container $c): void
    {
        $entityManager = $c->get(EntityManagerInterface::class);
        $passportControl = new PassportControl($entityManager);
        $middleware = new PassportControlMiddleware($passportControl);
        $c[PassportControl::class] = $passportControl;
        $c[PassportControlMiddleware::class] = $middleware;
    }

    public function registerConsoleCommands(Container $container): array
    {
        $passportControl = $container->get(PassportControl::class);
        $em = $container->get(EntityManagerInterface::class);

        return [
            new RoleCommand($passportControl),
            new PassportCommand($passportControl, $em),
        ];
    }

    public function getFixtures(): array
    {
        return [
            LoadRoles::class,
        ];
    }

    public function getRequiredPackages(): array
    {
        return [
            \Del\Passport\PassportPackage::class,
            PassportPackage::class,
        ];
    }
}
