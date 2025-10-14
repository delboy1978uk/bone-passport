<?php

declare(strict_types=1);

namespace Bone\Passport;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Contracts\Container\FixtureProviderInterface;
use Bone\Passport\Command\PassportCommand;
use Bone\Passport\Command\RoleCommand;
use Bone\Passport\Fixtures\LoadRoles;
use Del\Passport\PassportControl;
use Doctrine\ORM\EntityManager;

class PassportPackage implements RegistrationInterface, CommandRegistrationInterface, FixtureProviderInterface
{
    public function addToContainer(Container $c)
    {

    }

    public function registerConsoleCommands(Container $container): array
    {
        $passportControl = $container->get(PassportControl::class);
        $em = $container->get(EntityManager::class);

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
}
