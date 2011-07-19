<?php

namespace Doctrine\Bundle\OXMBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * Constructor.
     *
     * @param Boolean $debug The kernel.debug value
     */
    public function __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('doctrine_oxm');

        $this->addXmlEntityManagersSection($rootNode);
        $this->addStoragesSection($rootNode);
        
        $rootNode
            ->children()
                ->scalarNode('proxy_namespace')->defaultValue('Proxies')->end()
                ->scalarNode('proxy_dir')->defaultValue('%kernel.cache_dir%/doctrine/oxm/Proxies')->end()
                ->scalarNode('auto_generate_proxy_classes')->defaultFalse()->end()
                ->scalarNode('default_xml_entity_manager')->end()
                ->scalarNode('default_storage')->defaultValue('default')->end()
            ->end()
        ;     

        return $treeBuilder;
    }
    
        /**
     * Configures the "xml_entity_managers" section
     */
    private function addXmlEntityManagersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('xml_entity_manager')
            ->children()
                ->arrayNode('xml_entity_managers')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->treatNullLike(array())
                        ->children()
                            ->scalarNode('storage')->end()
                            ->scalarNode('auto_mapping')->defaultFalse()->end()
                            ->arrayNode('metadata_cache_driver')
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return !is_array($v); })
                                    ->then(function($v) { return array('type' => $v); })
                                ->end()
                                ->children()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('class')->end()
                                    ->scalarNode('host')->end()
                                    ->scalarNode('port')->end()
                                    ->scalarNode('instance_class')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->fixXmlConfig('mapping')
                        ->children()
                            ->arrayNode('mappings')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(function($v) { return array ('type' => $v); })
                                    ->end()
                                    ->treatNullLike(array())
                                    ->treatFalseLike(array('mapping' => false))
                                    ->performNoDeepMerging()
                                    ->children()
                                        ->scalarNode('mapping')->defaultValue(true)->end()
                                        ->scalarNode('type')->end()
                                        ->scalarNode('dir')->end()
                                        ->scalarNode('prefix')->end()
                                        ->scalarNode('alias')->end()
                                        ->booleanNode('is_bundle')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
        
    /**
     * Adds the configuration for the "storages" key
     */
    private function addStoragesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('storage')
            ->children()
                ->arrayNode('storages')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->performNoDeepMerging()
                        ->children()
                            ->scalarNode('type')->defaultValue('filesystem')->end()
                            ->scalarNode('path')->defaultValue('%kernel.root_dir%/doctrine-oxm-storage')->end()
                            ->scalarNode('extension')->defaultValue('xml')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }


}
