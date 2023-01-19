<?php

/**
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Controllers\Legacy;

use Exception;
use Ox\Core\Api\Request\RequestApiBuilder;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbObjectConfig;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Eai\CConfigurationEai;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\Controllers\ConfigurationController;

class CActorControllerLegacy extends CLegacyController
{
    /** @var string[] */
    private const MAPPING_NORME_RECEIVER = [
        "CReceiverHL7v2Config"      => 'HL7v2',
        "CReceiverHL7v3Config"      => 'HL7v3',
        "CReceiverHPrimSanteConfig" => 'H\'Santé',
        "CPhastDestinataireConfig"  => 'Phast',
        "CReceiverFHIRConfig"       => 'FHIR',
    ];

    /**
     * Migration configs
     *
     * @throws Exception
     */
    public function migrationConfigs(): void
    {
        $actor = CView::get('actor', 'str');
        CView::checkin();

        set_time_limit(240);

        /** @var CInteropActor $interop_actor */
        $interop_actor  = new $actor();
        $interop_actors = $interop_actor->loadList();

        $report        = [];
        $configs_actor = (new CConfigurationEai())->getAllConfigActors();

        if ($interop_actor instanceof CInteropSender) {
            $config_norme = CMbArray::getRecursive($configs_actor, "$actor eai");
            foreach ($interop_actors as $_actor) {
                $norms = [];

                // HL7
                $_actor->loadBackRefConfigHL7();
                if ($_actor->_ref_config_hl7 && $_actor->_ref_config_hl7->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_hl7, $config_norme, 'HL7'),
                        $_actor
                    );

                    $norms[] = 'HL7';
                }

                // H'XML
                $_actor->loadBackRefConfigHprimXML();
                if ($_actor->_ref_config_hprim && $_actor->_ref_config_hprim->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_hprim, $config_norme, 'hprimxml'),
                        $_actor
                    );

                    $norms[] = 'H\'XML';
                }

                // H'Santé
                $_actor->loadBackRefConfigHprimSante();
                if ($_actor->_ref_config_hprimsante && $_actor->_ref_config_hprimsante->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_hprimsante, $config_norme, 'hprimsante'),
                        $_actor
                    );

                    $norms[] = 'H\'Santé';
                }

                // CDA
                $_actor->loadBackRefConfigCDA();
                if ($_actor->_ref_config_cda && $_actor->_ref_config_cda->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_cda, $config_norme, 'cda'),
                        $_actor
                    );

                    $norms[] = 'CDA';
                }

                // Phast
                $_actor->loadBackRefConfigPhast();
                if ($_actor->_ref_config_phast && $_actor->_ref_config_phast->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_phast, $config_norme, 'phast'),
                        $_actor
                    );

                    $norms[] = 'Phast';
                }

                // Dicom
                $_actor->loadBackRefConfigDicom();
                if ($_actor->_ref_config_dicom && $_actor->_ref_config_dicom->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_dicom, $config_norme, 'dicom'),
                        $_actor
                    );

                    $norms[] = 'Dicom';
                }

                // FHIR
                $_actor->loadBackRefConfigFHIR();
                if ($_actor->_ref_config_fhir && $_actor->_ref_config_fhir->_id) {
                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_config_fhir, $config_norme, 'fhir'),
                        $_actor
                    );
                    $norms[] = 'FHIR';
                }

                $report[$_actor->_guid] = [
                    'actor' => $_actor,
                    'norm'  => $norms,
                ];
            }
        } else {
            foreach ($interop_actors as $_actor) {
                $_actor->loadRefObjectConfigs();
                $configs = CMbArray::getRecursive($configs_actor, "$_actor->_class eai");
                $norms   = [];

                if ($_actor->_ref_object_configs && $_actor->_ref_object_configs->_id) {
                    $configs = [];
                    $norms[] = self::MAPPING_NORME_RECEIVER[$_actor->_ref_object_configs->_class];

                    CConfiguration::setConfigs(
                        $this->prepareConfigs($_actor->_ref_object_configs, $configs),
                        $_actor
                    );
                }

                $report[$_actor->_guid] = [
                    'actor' => $_actor,
                    'norm'  => $norms,
                ];
            }
        }

        $this->renderSmarty('inc_migration_configs', [
            'report' => $report,
        ]);
    }

    /**
     * @param CMbObjectConfig $object_configs
     * @param array           $configs_map
     *
     * @return array
     */
    public function prepareConfigs(
        CMbObjectConfig $object_configs,
        array $configs_map,
        ?string $filter_suffix = null
    ): array {
        if ($filter_suffix) {
            $configs_map = array_filter($configs_map, function ($config) use ($filter_suffix) {
                return str_ends_with($config, $filter_suffix);
            },                          ARRAY_FILTER_USE_KEY);
        }

        $configs = [];
        foreach ($configs_map as $_family_key => $_family_configs) {
            foreach ($_family_configs as $_configs_name => $_configs_prop) {
                $path           = 'eai ' . $_family_key . ' ' . $_configs_name;
                $configs[$path] = $object_configs->$_configs_name;
            }
        }

        return $configs;
    }

    /**
     * Show configuration template for actor config
     */
    public function showConfigObjectValues(): void
    {
        CCanDo::checkEdit();
        $actor_guid = CView::get('object_guid', 'str');
        CView::checkin();

        [$actor_class, $actor_uid] = explode('-', $actor_guid);

        // retrieve all configuration actors
        $configs       = (new CConfigurationEai())->getAllConfigActors();
        $configs_actor = CMbArray::getRecursive($configs, "$actor_class eai", []);

        // compute all format availlable
        $config_sections = array_keys($configs_actor);
        $formats         = array_unique(
            array_map(function ($section_name) {
                return substr($section_name, strrpos($section_name, '-') + 1);
            }, $config_sections)
        );

        $this->renderSmarty(
            'inc_config_object_values',
            [
                'mode'         => CConfigurationModelManager::getConfigurationMode(),
                'module'       => 'eai',
                'object_class' => $actor_class,
                'actor_guid'   => $actor_guid,
                'formats'      => array_values($formats),
            ]
        );
    }

    /**
     * Export all configurations for an actor in the $format given
     */
    public function exportConfigs(): void
    {
        CCanDo::checkEdit();
        $actor_guid = CView::get('object_guid', 'str');
        $format     = CView::get('format', 'str');
        CView::checkin();

        [$actor_class, $actor_id] = explode('-', $actor_guid, 2);

        // retrieve all key of configurations actor
        $all_path = array_keys(
            CMbArray::flattenArrayKeys((new CConfigurationEai())->getAllConfigActors()[$actor_class])
        );

        $uri            = $this->generateUrl('system_get_configs');
        $configurations = [];
        // build all key of config actor in object CConfiguration
        foreach ($all_path as $path) {
            $path    = ltrim($path, ' ');
            $section = explode(' ', $path)[1];
            if (!str_ends_with($section, $format)) {
                continue;
            }

            $configurations[]            = $configuration = new CConfiguration();
            $configuration->feature      = $path;
        }

        // build RequestAPI
        $request = (new RequestApiBuilder())
            ->setUri($uri)
            ->setContent(json_encode((new Collection($configurations))->serialize()))
            ->buildRequestApi();
        $request->getRequest()->query->set('context', $actor_guid);

        // request route configurations
        $controller = new ConfigurationController();
        $response   = $controller->getConfigurations($request);

        if ($response->getStatusCode() !== 200) {
            CAppUI::stepAjax('CActorController-msg-fail export', UI_MSG_ERROR);
        }

        // parse content to transform in CConfiguration object (return array json api)
        $content        = $response->getContent();
        $data           = json_decode($content, true)['data'];
        $configurations = [];
        foreach ($data as $item) {
            $attributes             = $item['attributes'];
            $configurations[]       = $configuration = new CConfiguration();
            $configuration->feature = array_key_first($attributes);
            $configuration->value   = reset($attributes);
        }

        ob_clean();

        header("Content-Type: application/json");
        header("Content-Disposition: attachment; filename=\"config-actor-$format.json\"");

        // transform CConfiguration in json api (and remove meta)
        $values = (new Collection($configurations))->serialize();
        unset($values['meta']);

        echo json_encode($values);

        $this->rip();
    }

    /**
     * Import all configuration from a file for an actor
     *
     * @return void
     * @throws Exception
     */
    public function importConfigs(): void
    {
        CCanDo::checkEdit();
        $actor_guid = CView::post('actor_guid', 'str');
        $file       = isset($_FILES['import']) ? $_FILES['import'] : null;
        if (!empty($file)) {
            $contents = file_get_contents($file['tmp_name']);
        }
        CView::checkin();

        $actor = CStoredObject::loadFromGuid($actor_guid);
        if (!$actor || !$actor instanceof CInteropActor || !$actor->_id || !($contents ?? null)) {
            CAppUI::stepAjax('CActorController-msg-fail import', UI_MSG_ERROR);
        }

        $uri     = $this->generateUrl('system_set_configs');
        $request = (new RequestApiBuilder())
            ->setUri($uri)
            ->setMethod('PUT')
            ->setContent($contents)
            ->buildRequestApi();
        $request->getRequest()->query->set('context', $actor_guid);

        // request configurations
        $controller = new ConfigurationController();
        $response   = $controller->setConfigurations($request);

        if ($response->getStatusCode() !== 200) {
            echo CAppUI::tr('CActorController-msg-fail import');
        }

        echo CAppUI::tr('CConfiguration-msg-modify');
    }

    /**
     * Show template for import file configuration
     */
    public function showUploadFileConfig(): void
    {
        CCanDo::checkEdit();
        $actor_guid = CView::get('object_guid', 'str');
        CView::checkin();

        $this->renderSmarty(
            'import_config',
            [
                'actionType' => 'a',
                'action'     => 'importConfigs',
                'm'          => 'eai',
                'actor_guid' => $actor_guid,
            ]
        );
    }
}
