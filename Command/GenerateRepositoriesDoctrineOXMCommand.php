<?php

namespace Doctrine\Bundle\OXMBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\OXM\Tools\XmlEntityRepositoryGenerator;

/**
 * Command to generate repository classes for mapping information.
 *
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class GenerateRepositoriesDoctrineOXMCommand extends DoctrineOXMCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:oxm:generate:repositories')
            ->setDescription('Generate repository classes from your mapping information.')
            ->addArgument('bundle', InputArgument::REQUIRED, 'The bundle to initialize the repositories in.')
            ->addOption('xm-entity', null, InputOption::VALUE_OPTIONAL, 'The xml-entity class to generate the repository for (shortname without namespace).')
            ->setHelp(<<<EOT
The <info>doctrine:oxm:generate:repositories</info> command generates the configured dxml-entity repository classes from your mapping information:

  <info>./app/console doctrine:oxm:generate:repositories</info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundleName = $input->getArgument('bundle');
        $filterXmlEntity = $input->getOption('xml-entity');

        $foundBundle = $this->findBundle($bundleName);

        if ($metadatas = $this->getBundleMetadatas($foundBundle)) {
            $output->writeln(sprintf('Generating xml-entity repositories for "<info>%s</info>"', $foundBundle->getName()));
            $generator = new XmlEntityRepositoryGenerator();

            foreach ($metadatas as $metadata) {
                if ($filterDocument && $filterXmlEntity !== $metadata->reflClass->getShortname()) {
                    continue;
                }

                if ($metadata->customRepositoryClassName) {
                    if (strpos($metadata->customRepositoryClassName, $foundBundle->getNamespace()) === false) {
                        throw new \RuntimeException(
                            "Repository " . $metadata->customRepositoryClassName . " and bundle don't have a common namespace, ".
                            "generation failed because the target directory cannot be detected.");
                    }

                    $output->writeln(sprintf('  > <info>OK</info> generating <comment>%s</comment>', $metadata->customRepositoryClassName));
                    $generator->writeXmlEntityRepositoryClass($metadata->customRepositoryClassName, $this->findBasePathForBundle($foundBundle));
                } else {
                    $output->writeln(sprintf('  > <error>SKIP</error> no custom repository for <comment>%s</comment>', $metadata->name));
                }
            }
        } else {
            throw new \RuntimeException("Bundle " . $bundleName . " does not contain any mapped xml-entities.");
        }
    }
}
