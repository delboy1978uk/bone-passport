<?php

namespace Bone\Passport\Command;

use Bone\Exception;
use Del\Passport\Entity\PassportRole;
use Del\Passport\Entity\Role;
use Del\Passport\PassportControl;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class PassportCommand extends Command
{
    /** @var QuestionHelper $helper */
    private $helper;

    /** @var PassportControl $passportControl */
    private $passportControl;

    /** @var EntityManager $entityManager */
    private $entityManager;

    public function __construct(PassportControl $passportControl, EntityManager $entityManager)
    {
        $this->passportControl = $passportControl;
        $this->entityManager = $entityManager;
        parent::__construct('passport:admin');
    }

    /**
     * configure options
     */
    protected function configure()
    {
        $this->setDescription('User role admin.');
        $this->setHelp('Grant or revoke roles.');
        $this->addArgument('operation',InputArgument::REQUIRED,'grant or revoke');
        $this->addArgument('role', InputArgument::REQUIRED, 'The role name');
        $this->addArgument('userId', InputArgument::REQUIRED, 'The ID of the user');
        $this->addArgument('entityId', InputArgument::OPTIONAL, 'The ID of the entity, if any');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');
        $role = $input->getArgument('role');
        $id = $input->getArgument('userId');
        $entityId = $input->getArgument('entityId');

        switch ($operation) {
            case 'grant':
                $this->grantRole($role, $id, $entityId, $input, $output);
                break;
            case 'revoke':
                $this->removeRole($role, $id, $entityId, $input, $output);
                break;
            default:
                throw new Exception('Invalid operation, use either grant or revoke.');
        }

        return 0;
    }

    /**
     * @param string $name
     * @param int $id
     * @param int|null $entityId
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function grantRole(string $name, int $id, $entityId, InputInterface $input, OutputInterface $output): void
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
        $this->entityManager->flush($passport);
    }

    /**
     * @param string $name
     * @param int $id
     * @param int|null $entityId
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function removeRole(string $name, int $id, $entityId, InputInterface $input, OutputInterface $output): void
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
        $this->entityManager->flush($passport);
    }
}
