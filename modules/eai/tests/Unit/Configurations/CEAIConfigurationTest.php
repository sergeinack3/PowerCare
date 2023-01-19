<?php
/**
 * @package Core\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Configurations;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use Ox\Interop\Eai\CConfigurationEai;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Interop\Eai\Controllers\Legacy\CActorControllerLegacy;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CConfiguration;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CAppTest
 * @package Ox\Core\Tests\Unit
 */
class CEAIConfigurationTest extends OxUnitTestCase
{
    public function providerReceivers(): array
    {
        $configs = (new CConfigurationEai())->getAllConfigActors();

        return [
            'Receiver HL7v2'      => [
                'object_class' => 'CReceiverHL7v2',
                'config_class' => 'CReceiverHL7v2Config',
                'configs'      => $configs['CReceiverHL7v2']['eai'],
            ],
            'Receiver HL7v3'      => [
                'object_class' => 'CReceiverHL7v3',
                'config_class' => 'CReceiverHL7v3Config',
                'configs'      => $configs['CReceiverHL7v3']['eai'],
            ],
            'Receiver HPrimSante' => [
                'object_class' => 'CReceiverHprimSante',
                'config_class' => 'CReceiverHPrimSanteConfig',
                'configs'      => $configs['CReceiverHprimSante']['eai'],
            ],
            'Receiver Phast'      => [
                'object_class' => 'CPhastDestinataire',
                'config_class' => 'CPhastDestinataireConfig',
                'configs'      => $configs['CPhastDestinataire']['eai'],
            ],
            'Receiver FHIR'       => [
                'object_class' => 'CReceiverFHIR',
                'config_class' => 'CReceiverFHIRConfig',
                'configs'      => $configs['CReceiverFHIR']['eai'],
            ],
        ];
    }

    public function providerSenders(): array
    {
        $configs = (new CConfigurationEai())->getAllConfigActors();

        $filter_norme = function (string $actor_class, string $suffix) use ($configs): array {
            return array_filter(
                $configs[$actor_class]['eai'],
                function ($config_name) use ($suffix) {
                    return str_ends_with($config_name, $suffix);
                },
                ARRAY_FILTER_USE_KEY
            );
        };

        return [
            'Sender Config HL7'        => [
                'object_class' => 'CSenderFileSystem',
                'config_class' => 'CHL7Config',
                'configs'      => $filter_norme('CSenderFileSystem', 'HL7'),
            ],
            'Sender Config HPrimXML'   => [
                'object_class' => 'CSenderFileSystem',
                'config_class' => 'CHprimXMLConfig',
                'configs'      => $filter_norme('CSenderFileSystem', 'hprimxml'),
            ],
            'Sender Config HPrimSante' => [
                'object_class' => 'CSenderSOAP',
                'config_class' => 'CHPrimSanteConfig',
                'configs'      => $filter_norme('CSenderFileSystem', 'hprimsante'),
            ],
            'Sender Config CDA'        => [
                'object_class' => 'CSenderFTP',
                'config_class' => 'CCDAConfig',
                'configs'      => $filter_norme('CSenderFileSystem', 'cda'),
            ],
            'Sender Config Phast'      => [
                'object_class' => 'CSenderSOAP',
                'config_class' => 'CPhastConfig',
                'configs'      => $filter_norme('CSenderFileSystem', 'phast'),
            ],
            'Sender Config FHIR'       => [
                'object_class' => 'CSenderHTTP',
                'config_class' => 'CFHIRConfig',
                'configs'      => $filter_norme('CSenderFileSystem', 'fhir'),
            ],
        ];
    }

    /**
     * @dataProvider providerReceivers
     *
     * @param string $object_class
     * @param string $config_class
     *
     * @return void
     * @throws CMbException
     */
    public function testPrepareConfigsReceiver(string $object_class, string $config_class, array $configs)
    {
        $receiver           = new $object_class();
        $receiver->nom      = 'test_migration_configs_receiver';
        $receiver->group_id = CGroups::loadCurrent()->_id;
        $receiver->loadMatchingObject();
        if ($msg = $receiver->store()) {
            throw new CMbException($msg);
        }

        if (!$receiver->_id) {
            throw new CMbException('Object not found');
        }

        $receiver->loadRefObjectConfigs();
        $configs_receiver = $receiver->_ref_object_configs;

        if (!$receiver->_ref_object_configs || !$receiver->_ref_object_configs->_id) {
            $configs_receiver            = new $config_class();
            $configs_receiver->object_id = $receiver->_id;
            if ($msg = $configs_receiver->store()) {
                throw new CMbException($msg);
            }
            $receiver->_ref_object_configs = $configs_receiver;
        }

        $controller = new CActorControllerLegacy();
        CConfiguration::setConfigs(
            $controller->prepareConfigs($receiver->_ref_object_configs, $configs),
            $receiver
        );

        foreach ($configs as $_family_key => $_family_configs) {
            foreach ($_family_configs as $_configs_name => $_configs_prop) {
                $path = 'eai ' . $_family_key . ' ' . $_configs_name;

                $this->assertEquals(CAppUI::conf($path, $receiver), $receiver->_ref_object_configs->$_configs_name);
            }
        }

        // Suppression des configs qui ont été créés
        $configuration = new CConfiguration();
        $ds            = $configuration->getDS();
        $where         = [
            'object_id'    => $ds->prepare('= ?', $receiver->_id),
            'object_class' => $ds->prepare('= ?', $receiver->_class),
        ];

        $ids = $configuration->loadIds($where);
        $configuration->deleteAll($ids);

        $configs_receiver->delete();
        $receiver->delete();
    }

    /**
     * @dataProvider providerSenders
     *
     * @param string $object_class
     * @param string $config_class
     *
     * @return void
     * @throws CMbException
     */
    public function testPrepareConfigsSender(string $object_class, string $config_class, array $configs)
    {
        /** @var CInteropSender $sender */
        $sender           = new $object_class();
        $sender->nom      = 'test_migration_configs_sender';
        $sender->group_id = CGroups::loadCurrent()->_id;
        $sender->loadMatchingObject();
        if ($msg = $sender->store()) {
            throw new CMbException($msg);
        }

        if (!$sender->_id) {
            throw new CMbException('Object not found');
        }

        $object_configs = null;
        switch ($config_class) {
            case 'CHL7Config':
                $object_configs = $sender->loadBackRefConfigHL7();
                break;
            case 'CHprimXMLConfig':
                $object_configs = $sender->loadBackRefConfigHprimXML();
                break;
            case 'CHPrimSanteConfig':
                $object_configs = $sender->loadBackRefConfigHprimSante();
                break;
            case 'CCDAConfig':
                $object_configs = $sender->loadBackRefConfigCDA();
                break;
            case 'CPhastConfig':
                $object_configs = $sender->loadBackRefConfigPhast();
                break;
            case 'CFHIRConfig':
                $object_configs = $sender->loadBackRefConfigFHIR();
                break;
        }

        if (!$object_configs || !$object_configs->_id) {
            $object_configs               = new $config_class();
            $object_configs->sender_id    = $sender->_id;
            $object_configs->sender_class = $sender->_class;
            if ($msg = $object_configs->store()) {
                throw new CMbException($msg);
            }
        }

        $controller = new CActorControllerLegacy();
        CConfiguration::setConfigs(
            $controller->prepareConfigs($object_configs, $configs),
            $sender
        );

        foreach ($configs as $_family_key => $_family_configs) {
            foreach ($_family_configs as $_configs_name => $_configs_prop) {
                $path = 'eai ' . $_family_key . ' ' . $_configs_name;

                $this->assertEquals(CAppUI::conf($path, $sender), $object_configs->$_configs_name);
            }
        }

        // Suppression des configs qui ont été créés
        $configuration = new CConfiguration();
        $ds            = $configuration->getDS();
        $where         = [
            'object_id'    => $ds->prepare('= ?', $sender->_id),
            'object_class' => $ds->prepare('= ?', $sender->_class),
        ];

        $ids = $configuration->loadIds($where);
        $configuration->deleteAll($ids);

        $object_configs->delete();
        $sender->delete();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function providerConfigurationActorEndWithSuffix(): array
    {
        /** @var ConfigurationActorInterface[] $config_classes */
        $config_classes = CClassMap::getInstance()->getClassChildren(ConfigurationActorInterface::class, true, true);

        $data = [];
        foreach ($config_classes as $config_class) {
            $suffixes = $config_class->getSuffixesSectionActor();

            $configs = $config_class->getConfigurationsActor();
            foreach ($configs as $actor_class => $section_configs) {
                foreach ($section_configs as $section_name => $values) {
                    $data[get_class($config_class) . $actor_class . $section_name] = [
                        'config' => $section_name,
                        'suffixes' => $suffixes,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Each section for configurations actor should be suffixed by his format.
     *
     * @dataProvider providerConfigurationActorEndWithSuffix
     *
     * @param string $config
     * @param string $suffix
     *
     * @return void
     */
    public function testConfigurationActorEndWithSuffix(string $config, array $suffixes): void
    {
        $suffix = implode('|', $suffixes);
        $pattern = sprintf(ConfigurationActorInterface::REGEX_MATCH_SUFFIX, $suffix);

        $this->assertTrue(
            preg_match("/$pattern/", $config) === 1,
            'The section of configuration for eai actor should end with the suffix : ' . $suffix
        );
    }
}
