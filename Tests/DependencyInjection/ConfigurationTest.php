<?php

namespace Doctrine\Bundle\OXMBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\OXMBundle\DependencyInjection\Configuration;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $processor = new Processor();
        $configuration = new Configuration(false);
        $options = $processor->processConfiguration($configuration, array());

        $defaults = array(
            'auto_generate_proxy_classes'    => false,
            'default_storage'                => 'default',
            'xml_entity_managers'            => array(),
            'storages'                       => array(),
            'proxy_dir'                      => '%kernel.cache_dir%/doctrine/oxm/Proxies',
            'proxy_namespace'                => 'Proxies',
        );

        foreach ($defaults as $key => $default) {
            $this->assertTrue(array_key_exists($key, $options), sprintf('The default "%s" exists', $key));
            $this->assertEquals($default, $options[$key]);

            unset($options[$key]);
        }

        if (count($options)) {
            $this->fail('Extra defaults were returned: '. print_r($options, true));
        }
    }

    /**
     * Tests a full configuration.
     *
     * @dataProvider fullConfigurationProvider
     */
    public function testFullConfiguration($config)
    {
        $processor = new Processor();
        $configuration = new Configuration(false);
        $options = $processor->processConfiguration($configuration, array($config));

        $expected = array(
            'proxy_dir'                      => '%kernel.cache_dir%/doctrine/oxm/Proxies',
            'proxy_namespace'                => 'Test_Proxies',
            'auto_generate_proxy_classes'    => true,
            'default_xml_entity_manager'     => 'default_xem_name',
            'default_storage'                => 'default_storage_name',
            'storages'   => array(
                'storg1'       => array(
                    'path'     => '%kernel.root_dir%/doctrine-oxm-storage/storg1',
                    'type'     => 'filesystem',
                    'extension' => 'xml',
                ),
                'storg2'       => array(
                    'path'     => '%kernel.root_dir%/doctrine-oxm-storage/storg2',
                    'type'     => 'filesystem',
                    'extension' => 'xml',
                ),
            ),
            'xml_entity_managers' => array(
                'xem1' => array(
                    'auto_mapping' => false,
                    'metadata_cache_driver' => array(
                        'type' => 'apc',
                    ),
                    'mappings' => array(
                        'FooBundle' => array(
                            'type'    => 'annotations',
                            'mapping' => true,
                        ),
                    ),
                ),
                'xem2' => array(
                    'storage'      => 'storg2',
                    'auto_mapping' => false,
                    'metadata_cache_driver' => array(
                        'type' => 'apc',
                    ),
                    'mappings' => array(
                        'BarBundle' => array(
                            'type'      => 'yml',
                            'dir'       => '%kernel.cache_dir%',
                            'prefix'    => 'prefix_val',
                            'alias'     => 'alias_val',
                            'is_bundle' => false,
                            'mapping'   => true,
                        )
                    ),
                )
            )
        );

        $this->assertEquals($expected, $options);
    }

    public function fullConfigurationProvider()
    {
      $yaml = Yaml::parse(__DIR__.'/Fixtures/config/yml/full.yml');
      $yaml = $yaml['doctrine_oxm'];

       return array(
           array($yaml),
       );
    }

    /**
     * @dataProvider optionProvider
     * @param array $configs The source array of configuration arrays
     * @param array $correctValues A key-value pair of end values to check
     */
    public function testMergeOptions(array $configs, array $correctValues)
    {
        $processor = new Processor();
        $configuration = new Configuration(false);
        $options = $processor->processConfiguration($configuration, $configs);

        foreach ($correctValues as $key => $correctVal)
        {
            $this->assertEquals($correctVal, $options[$key]);
        }
    }

    public function optionProvider()
    {
        $cases = array();

        // single config, testing normal option setting
        $cases[] = array(
            array(
                array('default_xml_entity_manager' => 'foo'),
            ),
            array('default_xml_entity_manager' => 'foo')
        );

        // single config, testing normal option setting with dashes
        $cases[] = array(
            array(
                array('default-xml-entity-manager' => 'bar'),
            ),
            array('default_xml_entity_manager' => 'bar')
        );

        // testing the normal override merging - the later config array wins
        $cases[] = array(
            array(
                array('default_xml_entity_manager' => 'foo'),
                array('default_xml_entity_manager' => 'baz'),
            ),
            array('default_xml_entity_manager' => 'baz')
        );

        // mappings are merged non-recursively.
        $cases[] = array(
            array(
                array('xml_entity_managers' => array('default' => array('mappings' => array('foomap' => array('type' => 'val1'), 'barmap' => array('dir' => 'val2'))))),
                array('xml_entity_managers' => array('default' => array('mappings' => array('barmap' => array('prefix' => 'val3'))))),
            ),
            array('xml_entity_managers' => array('default' => array('auto_mapping' => false, 'mappings' => array('foomap' => array('type' => 'val1', 'mapping' => true), 'barmap' => array('prefix' => 'val3', 'mapping' => true))))),
        );

        // connections are merged non-recursively.
//        $cases[] = array(
//            array(
//                array('storages' => array('foostor' => array('path' => '%kernel.root_dir%/doctrine-oxm-storage/stor2'))),
//                array('storages' => array('barstor' => array('extension' => 'stor.xml'))),
//            ),
//            array('storages' => array(
//                'foostor' => array(),
//                'barstor' => array(),
//            )),
//        );

        // managers are merged non-recursively.
        $cases[] = array(
            array(
                array('xml_entity_managers' => array('fooxem' => array('storage' => 'val1'), 'barxem' => array())),
                array('xml_entity_managers' => array('barxem' => array('storage' => 'val3'))),
            ),
            array('xml_entity_managers' => array(
                'fooxem' => array('storage' => 'val1', 'auto_mapping' => false, 'mappings' => array()),
                'barxem' => array('storage' => 'val3', 'auto_mapping' => false, 'mappings' => array()),
            )),
        );

        return $cases;
    }

    /**
     * @dataProvider getNormalizationTests
     */
    public function testNormalizeOptions(array $config, $targetKey, array $normalized)
    {
        $processor = new Processor();
        $configuration = new Configuration(false);
        $options = $processor->processConfiguration($configuration, array($config));
        $this->assertSame($normalized, $options[$targetKey]);
    }

    public function getNormalizationTests()
    {
        return array(
            // storage versus storages (id is the identifier)
            // @todo FIX tests
//            array(
//                array('storages' => array(
//                    array('path' => '%kernel.root_dir%/doctrine-oxm-storage/stor1', 'id' => 'foo'),
//                    array('path' => '%kernel.root_dir%/doctrine-oxm-storage/stor2', 'id' => 'bar'),
//                )),
//                'storages',
//                array(
//                    'foo' => array('path' => '%kernel.root_dir%/doctrine-oxm-storage/stor1'),
//                    'bar' => array('path' => '%kernel.root_dir%/doctrine-oxm-storage/stor2'),
//                ),
//            ),
            // xml_entity_manager versus xml_entity_managers (id is the identifier)
            array(
                array('xml_entity_manager' => array(
                    array('storage' => 'conn1', 'id' => 'foo'),
                    array('storage' => 'conn2', 'id' => 'bar'),
                )),
                'xml_entity_managers',
                array(
                    'foo' => array('storage' => 'conn1', 'auto_mapping' => false, 'mappings' => array()),
                    'bar' => array('storage' => 'conn2', 'auto_mapping' => false, 'mappings' => array()),
                ),
            ),
            // mapping configuration that's beneath a specific xml-entity manager
            array(
                array('xml_entity_manager' => array(
                    array('id' => 'foo', 'storage' => 'conn1', 'mapping' => array(
                        'type' => 'xml', 'name' => 'foo-mapping'
                    )),
                )),
                'xml_entity_managers',
                array(
                    'foo' => array(
                        'storage'   => 'conn1', 
                        'mappings'     => array('foo-mapping' => array('type' => 'xml', 'mapping' => true)),
                        'auto_mapping' => false,
                    ),
                ),
            ),
        );
    }
}
