<?php

namespace Bone\Passport\Command;

use Bone\Console\Command;
use Bone\Exception;
use Del\Passport\Entity\PassportRole;
use Del\Passport\Entity\Role;
use Del\Passport\PassportControl;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PassportCommand extends Command
{
    public function __construct(
        private PassportControl $passportControl,
        private EntityManager $entityManager
    ) {
        parent::__construct('passport:admin');
    }

    protected function configure(): void
    {
        $this->setDescription('User role admin.');
        $this->setHelp('Grant or revoke roles.');
        $this->addArgument('operation',InputArgument::REQUIRED,'grant or revoke');
        $this->addArgument('role', InputArgument::REQUIRED, 'The role name');
        $this->addArgument('userId', InputArgument::REQUIRED, 'The ID of the user');
        $this->addArgument('entityId', InputArgument::OPTIONAL, 'The ID of the entity, if any');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $operation = $input->getArgument('operation');
        $role = $input->getArgument('role');
        $id = $input->getArgument('userId');
        $entityId = $input->getArgument('entityId');

        switch ($operation) {
            case 'grant':
                $this->grantRole($role, $id, $entityId, $output);
                break;
            case 'revoke':
                $this->removeRole($role, $id, $entityId, $output);
                break;
            default:
                throw new Exception('Invalid operation, use either grant or revoke.');
        }

        return self::SUCCESS;
    }

    private function grantRole(string $name, int $id, $entityId, OutputInterface $output): void
    {
        if (!$role = $this->passportControl->findRole($name)) {
            throw new Exception('Role not found in database');
        }

        $output->writeln('Granting user ' . $id . ' the ' . $name . ' role.');
        $passport = new PassportRole();
        $passport->setRole($role);
        $passport->setUserId($id);
        $entityId ? $passport->setEntityId($entityId) : null;

        $this->entityManager->persist($passport);
        $this->entityManager->flush();
    }

    private function removeRole(string $name, int $id, $entityId, OutputInterface $output): void
    {
        if (!$role = $this->passportControl->findRole($name)) {
            throw new Exception('Role not found in database');
        }

        $passport = $this->passportControl->findPassportRole($role->getId(), $id, $entityId);

        if (!$passport) {
            throw new Exception('Passport not found');
        }

        $output->writeln('Removing ' . $name . ' role.');
        $this->entityManager->remove($passport);
        $this->entityManager->flush();
    }
}
