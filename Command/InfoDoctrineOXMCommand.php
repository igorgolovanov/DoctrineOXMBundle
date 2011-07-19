<?php

namespace Doctrine\Bundle\OXMBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show information about mapped xml-entities.
 *
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class InfoDoctrineOXMCommand extends DoctrineOXMCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:oxm:mapping:info')
            ->addOption('xem', null, InputOption::VALUE_OPTIONAL, 'The xml-entity manager to use for this command.')
            ->setDescription('Show basic information about all mapped xml-entities.')
            ->setHelp(<<<EOT
The <info>doctrine:oxm:mapping:info</info> shows basic information about which
xml-entities exist and possibly if their mapping information contains errors or not.

  <info>./app/console doctrine:oxm:mapping:info</info>

If you are using multiple xml-entity managers you can pick your choice with the <info>--xem</info> option:

  <info>./app/console doctrine:oxm:mapping:info --xem=default</info>
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xmlEntityManagerName = $input->getOption('xem') ?
            $input->getOption('xem') :
            $this->getContainer()->getParameter('doctrine.oxm.default_xml_entity_manager');

        $xmlEntityManagerService = sprintf('doctrine.oxm.%s_xml_entity_manager', $xmlEntityManagerName);

        /* @var $xmlEntityManager Doctrine\OXM\XmlEntityManager */
        $xmlEntityManager = $this->getContainer()->get($xmlEntityManagerService);

        $xmlEntityClassNames = $xmlEntityManager->getConfiguration()
                                          ->getMetadataDriverImpl()
                                          ->getAllClassNames();

        if (!$xmlEntityClassNames) {
            throw new \Exception(
                'You do not have any mapped Doctrine OXM xml-entities for any of your bundles. '.
                'Create a class inside the XmlEntity namespace of any of your bundles and provide '.
                'mapping information for it with Annotations directly in the classes doc blocks '.
                'or with XML/YAML in your bundles Resources/config/doctrine/ directory.'
            );
        }

        $output->write(sprintf("Found <info>%d</info> xml-entities mapped in xml-entity manager <info>%s</info>:\n",
            count($xmlEntityClassNames), $xmlEntityManagerName), true);

        foreach ($xmlEntityClassNames AS $xmlEntityClassName) {
            try {
                $cm = $xmlEntityManager->getClassMetadata($xmlEntityClassName);
                $output->write("<info>[OK]</info>   " . $xmlEntityClassName, true);
            } catch(\Exception $e) {
                $output->write("<error>[FAIL]</error> " . $xmlEntityClassName, true);
                $output->write("<comment>" . $e->getMessage()."</comment>", true);
                $output->write("", true);
            }
        }
    }
}
