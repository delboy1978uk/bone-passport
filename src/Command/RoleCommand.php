<?php

namespace Bone\Passport\Command;

use Bone\Exception;
use Del\Passport\Entity\Role;
use Del\Passport\PassportControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class RoleCommand extends Command
{
    /** @var QuestionHelper $helper */
    private $helper;

    /** @var PassportControl $passportControl */
    private $passportControl;

    public function __construct(PassportControl $passportControl)
    {
        $this->passportControl = $passportControl;
        parent::__construct('passport:role');
    }

    /**
     * configure options
     */
    protected function configure()
    {
        $this->setDescription('Manages roles.');
        $this->setHelp('Create or remove roles.');
        $this->addArgument('operation',InputArgument::REQUIRED,'add or remove');
        $this->addArgument('role', InputArgument::REQUIRED, 'The role name');
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

        switch ($operation) {
            case 'add':
                $this->addRole($role, $input, $output);
                break;
            case 'remove':
                $this->removeRole($role, $input, $output);
                break;
            default:
                throw new Exception('Invalid operation, use either add or remove.');
        }

        return 0;
    }

    /**
     * @param string $name
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function addRole(string $name, InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Creating ' . $name . ' role.');
        $helper = $this->getHelper('question');
        $question = new Question('Type in the parent role that manages this role, if any.  ', false);
        $parent = $helper->ask($input, $output, $question);
        $question = new Question('Type in the class this role manages, if any.  ', false);
        $class = $helper->ask($input, $output, $question);
        $role = new Role();
        $role->setRoleName($name);

        if ($parent && $parentRole = $this->passportControl->findRole($parent)) {
            $role->setParentRole($parentRole);
        }

        if ($class) {
            $role->setClass($class);
        }

        $this->passportControl->createNewRole($role);
    }

    /**
     * @param string $name
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function removeRole(string $name, InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Removing ' . $name . ' role.');

        if ($role = $this->passportControl->findRole($name)) {
            $role->setRoleName($name);
            $this->passportControl->createNewRole($role);

            return;
        }

        throw new Exception('Role not found in database');
    }
}
