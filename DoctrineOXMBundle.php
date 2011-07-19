<?php

namespace Doctrine\Bundle\OXMBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\OXMBundle\DependencyInjection\Compiler\EventManagerPass;
use Doctrine\Bundle\OXMBundle\DependencyInjection\Compiler\CreateProxyDirectoryPass;
use Doctrine\Bundle\OXMBundle\DependencyInjection\DoctrineOXMExtension;

/**
 * Doctrine OXM bundle.
 * 
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class DoctrineOXMBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventManagerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new CreateProxyDirectoryPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
    
    public function getContainerExtension()
    {
        return new DoctrineOXMExtension();
    }
}
