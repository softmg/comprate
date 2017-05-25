<?php

namespace ParsingBundle\Command;

use ApiBundle\RequestObject\CommandRequestInjector;
use ApiBundle\RequestObject\IRequestInjectableCommand;
use ApiBundle\RequestObject\ParserRequest;
use ApiBundle\RequestObject\RequestObjectErrors;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RunParserCommand extends ContainerAwareCommand
{
    /**
     * @var ParserRequest
     */
    private $parserRequest;

    /**
     * @var ConstraintViolationListInterface
     */
    private $parserRequestErrors;

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

        $parser = $this->getContainer()->get("parsing.{$this->parserRequest->site_code}");

        if (!$parser) {
            $output->writeln("Parser with site_code $siteCode not found");
            exit;
        }
        
        $parser->run();

        $output->writeln('Command result.');
    }

    /**
     * @param ParserRequest $parserRequest
     * @param ConstraintViolationListInterface $errors
     */
    public function setParserRequest(ParserRequest $parserRequest, ConstraintViolationListInterface $errors)
    {
        $this->parserRequest = $parserRequest;
        $this->parserRequestErrors = $errors;
    }
}
