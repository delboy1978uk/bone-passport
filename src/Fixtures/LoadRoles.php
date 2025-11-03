<?php

declare(strict_types=1);

namespace Bone\Passport\Fixtures;

use Del\Passport\Entity\PassportRole;
use Del\Passport\Entity\Role;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadRoles implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $root = new Role();
        $root->setRoleName('superuser');
        $manager->persist($root);
        $manager->flush();

        $admin = new Role();
        $admin->setRoleName('admin');
        $admin->setParentRole($root);
        $manager->persist($admin);
        $manager->flush();

        $passportRole = new PassportRole();
        $passportRole->setUserId(1);
        $passportRole->setApprovedById(1);
        $passportRole->setRole($root);
        $manager->persist($passportRole);
        $manager->flush();

        $passportRole = new PassportRole();
        $passportRole->setUserId(2);
        $passportRole->setApprovedById(1);
        $passportRole->setRole($admin);
        $manager->persist($passportRole);
        $manager->flush();
    }
}
