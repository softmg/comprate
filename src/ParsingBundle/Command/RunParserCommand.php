<?php

namespace ParsingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunParserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('parser:run')
            ->setDescription('run parser')
            ->addArgument('site_code', InputArgument::REQUIRED, 'Site code from ParsingBundle\Entity\ParsingSite')
            //->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $siteCode = $input->getArgument('site_code');

        $parser = $this->getContainer()->get("parsing.$siteCode");
        if (!$parser) {
            $output->writeln("Parser with site_code $siteCode not found");
            exit;
        }
        
        $parser->run();

        $output->writeln('Command result.');
    }
}
