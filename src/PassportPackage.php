<?php

declare(strict_types=1);

namespace Bone\Passport;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Passport\Command\PassportCommand;
use Bone\Passport\Command\RoleCommand;
use Del\Passport\PassportControl;
use Doctrine\ORM\EntityManager;

class PassportPackage implements RegistrationInterface, CommandRegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {

    }

    /**
     * @param Container $container
     * @return array
     */
    public function registerConsoleCommands(Container $container): array
    {
        $passportControl = $container->get(PassportControl::class);
        $em = $container->get(EntityManager::class);

        return [
            new RoleCommand($passportControl),
            new PassportCommand($passportControl, $em),
        ];
    }

}
