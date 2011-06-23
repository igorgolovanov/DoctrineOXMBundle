<?php

namespace Go\DoctrineOXMBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Go\DoctrineOXMBundle\DependencyInjection\Compiler\EventManagerPass;

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
    }
}
