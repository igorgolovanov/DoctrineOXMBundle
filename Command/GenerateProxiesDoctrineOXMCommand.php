<?php

namespace Doctrine\Bundle\OXMBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\OXM\Tools\Console\Command\GenerateProxiesCommand;

/**
 * Generate the Doctrine OXM xml-entitty proxies to your cache directory.
 *
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class GenerateProxiesDoctrineOXMCommand extends GenerateProxiesCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:oxm:generate:proxies')
            ->addOption('xem', null, InputOption::VALUE_OPTIONAL, 'The xml-entity manager to use for this command.')
            ->setHelp(<<<EOT
The <info>doctrine:mongodb:generate:proxies</info> command generates proxy classes for your default xml-entity manager:

  <info>./app/console doctrine:oxm:generate:proxies</info>

You can specify the xml-entity manager you want to generate the proxies for:

  <info>./app/console doctrine:oxm:generate:proxies --xem=name</info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineOXMCommand::setApplicationXmlEntityManager($this->getApplication(), $input->getOption('xem'));

        return parent::execute($input, $output);
    }
}
