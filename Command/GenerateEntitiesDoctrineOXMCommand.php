<?php

namespace Doctrine\Bundle\OXMBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Generate xml-entity classes from mapping information
 *
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class GenerateEntitiesDoctrineOXMCommand extends DoctrineOXMCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:oxm:generate:xml-entities')
            ->setDescription('Generate xml-entity classes and method stubs from your mapping information.')
            ->addArgument('bundle', InputArgument::REQUIRED, 'The bundle to initialize the xml-entity or xml-entities in.')
            ->addOption('xml-entity', null, InputOption::VALUE_OPTIONAL, 'The xml-entity class to initialize (shortname without namespace).')
            ->setHelp(<<<EOT
The <info>doctrine:oxm:generate:xml-entities</info> command generates xml-entity classes and method stubs from your mapping information:

You have to limit generation of xml-entities to an individual bundle:

  <info>./app/console doctrine:oxm:generate:xml-entities MyCustomBundle</info>

Alternatively, you can limit generation to a single xml-entity within a bundle:

  <info>./app/console doctrine:oxm:generate:xml-entities "MyCustomBundle" --xml-entity="User"</info>

You have to specify the shortname (without namespace) of the xml-entity you want to filter for.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundleName = $input->getArgument('bundle');
        $filterXmlEntity = $input->getOption('xml-entity');

        $foundBundle = $this->findBundle($bundleName);

        if ($metadatas = $this->getBundleMetadatas($foundBundle)) {
            $output->writeln(sprintf('Generating xml-entities for "<info>%s</info>"', $foundBundle->getName()));
            $xmlEntityGenerator = $this->getXmlEntityGenerator();

            foreach ($metadatas as $metadata) {
                if ($filterXmlEntity && $metadata->reflClass->getShortName() != $filterXmlEntity) {
                    continue;
                }

                if (strpos($metadata->name, $foundBundle->getNamespace()) === false) {
                    throw new \RuntimeException(
                        "XmlEntity " . $metadata->name . " and bundle don't have a common namespace, ".
                        "generation failed because the target directory cannot be detected.");
                }

                $output->writeln(sprintf('  > generating <comment>%s</comment>', $metadata->name));
                $xmlEntityGenerator->generate(array($metadata), $this->findBasePathForBundle($foundBundle));
            }
        } else {
            throw new \RuntimeException("Bundle " . $bundleName . " does not contain any mapped xml-entities.");
        }
    }
}
