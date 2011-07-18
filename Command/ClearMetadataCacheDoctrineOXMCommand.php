<?php

namespace Doctrine\Bundle\OXMBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\OXM\Tools\Console\Command\ClearCache\MetadataCommand;

/**
 * Command to clear the metadata cache of the various cache drivers.
 *
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class ClearMetadataCacheDoctrineOXMCommand extends MetadataCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:oxm:cache:clear-metadata')
            ->setDescription('Clear all metadata cache for a xml-entity manager.')
            ->addOption('xem', null, InputOption::VALUE_OPTIONAL, 'The xml-entity manager to use for this command.')
            ->setHelp(<<<EOT
The <info>doctrine:oxm:cache:clear-metadata</info> command clears all metadata cache for the default xml-entity manager:

  <info>./app/console doctrine:oxm:cache:clear-metadata</info>

You can also optionally specify the <comment>--xem</comment> option to specify which xml-entity manager to clear the cache for:

  <info>./app/console doctrine:oxm:cache:clear-metadata --xem=default</info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineOXMCommand::setApplicationXmlEntityManager($this->getApplication(), $input->getOption('xem'));

        return parent::execute($input, $output);
    }
}