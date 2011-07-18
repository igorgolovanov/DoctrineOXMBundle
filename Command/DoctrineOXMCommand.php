<?php

namespace Doctrine\Bundle\OXMBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\OXM\Tools\Console\Helper\XmlEntityManagerHelper;
use Doctrine\OXM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\OXM\Tools\XmlEntityGenerator;

/**
 * Base class for Doctrine OXM console commands to extend.
 *
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
abstract class DoctrineOXMCommand extends ContainerAwareCommand
{
    public static function setApplicationXmlEntityManager(Application $application, $xemName)
    {
        $container = $application->getKernel()->getContainer();
        $xemName = $xemName ? $xemName : 'default';
        $xemServiceName = sprintf('doctrine.oxm.%s_xml_entity_manager', $xemName);
        if (!$container->has($xemServiceName)) {
            throw new \InvalidArgumentException(sprintf('Could not find Doctrine OXM XmlEntityManager named "%s"', $xemName));
        }

        $xem = $container->get($xemServiceName);
        $helperSet = $application->getHelperSet();
        $helperSet->set(new XmlEntityManagerHelper($xem), 'xem');
    }

    protected function getXmlEntityGenerator()
    {
        $xmlEntityGenerator = new XmlEntityGenerator();
        $xmlEntityGenerator->setGenerateAnnotations(false);
        $xmlEntityGenerator->setGenerateStubMethods(true);
        $xmlEntityGenerator->setRegenerateXmlEntityIfExists(false);
        $xmlEntityGenerator->setUpdateXmlEntityIfExists(true);
        $xmlEntityGenerator->setNumSpaces(4);
        return $xmlEntityGenerator;
    }

    protected function getDoctrineXmlEntityManagers()
    {
        $xmlEntityManagerNames = $this->getContainer()->getParameter('doctrine.oxm.xml_entity_managers');
        $xmlEntityManagers = array();
        foreach ($xmlEntityManagerNames as $xmlEntityManagerName) {
            $xem = $this->getContainer()->get(sprintf('doctrine.oxm.%s_xml_entity_manager', $xmlEntityManagerName));
            $xmlEntityManagers[] = $xem;
        }
        return $xmlEntityManagers;
    }

    protected function getBundleMetadatas(Bundle $bundle)
    {
        $namespace = $bundle->getNamespace();
        $bundleMetadatas = array();
        $xmlEntityManagers = $this->getDoctrineXmlEntityManagers();
        foreach ($xmlEntityManagers as $key => $xem) {
            $cmf = new DisconnectedClassMetadataFactory();
            $cmf->setXmlEntityManager($xem);
            $cmf->setConfiguration($xem->getConfiguration());
            $metadatas = $cmf->getAllMetadata();
            foreach ($metadatas as $metadata) {
                if (strpos($metadata->name, $namespace) === 0) {
                    $bundleMetadatas[$metadata->name] = $metadata;
                }
            }
        }

        return $bundleMetadatas;
    }

    protected function findBundle($bundleName)
    {
        $foundBundle = false;
        foreach ($this->getApplication()->getKernel()->getBundles() as $bundle) {
            /* @var $bundle Bundle */
            if (strtolower($bundleName) == strtolower($bundle->getName())) {
                $foundBundle = $bundle;
                break;
            }
        }

        if (!$foundBundle) {
            throw new \InvalidArgumentException("No bundle " . $bundleName . " was found.");
        }

        return $foundBundle;
    }

    /**
     * Transform classname to a path $foundBundle substract it to get the destination
     *
     * @param Bundle $bundle
     * @return string
     */
    protected function findBasePathForBundle($bundle)
    {
        $path = str_replace('\\', '/', $bundle->getNamespace());
        $search = str_replace('\\', '/', $bundle->getPath());
        $destination = str_replace('/'.$path, '', $search, $c);

        if ($c != 1) {
            throw new \RuntimeException(sprintf('Can\'t find base path for bundle (path: "%s", destination: "%s").', $path, $destination));
        }

        return $destination;
    }
}
