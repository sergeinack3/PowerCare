<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Utility\FilterableTrait;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Exception\AccessDeniedException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\ConfigurationManager;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ConfigurationController extends CController
{
    use FilterableTrait;

    private string $mod_name;

    private array $model = [];

    private array $available_contexts = [];

    private array $configurations = [];

    /**
     * @throws ApiException|AccessDeniedException
     * @throws Exception
     * @api
     */
    public function listConfigurations(string $mod_name, RequestApi $request_api): Response
    {
        $this->mod_name = $this->getActiveModule($mod_name);

        $module = CModule::getActive($this->mod_name);
        if (!$this->checkPermRead($module)) {
            throw new AccessDeniedException("Cannot access module '{$mod_name}'");
        }

        $configurations = [
            'instance' => $this->getInstanceConfigurations($request_api),
            'static'   => $this->getStaticConfigurations($request_api),
            'context'  => $this->getContextualConfigurations($request_api),
        ];

        $ressource = new Item($configurations);
        $ressource->setType('configurations');

        return $this->renderApiResponse($ressource);
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     * @api
     */
    public function getConfigurations(RequestApi $request_api): Response
    {
        $data = $this->getParamsAndLoad($request_api);

        foreach ($request_api->getResource()->getItems() as $_config) {
            /** @var CConfiguration $configuration */
            $configuration = $_config->createModelObject(CConfiguration::class, true)
                ->hydrateObject([CModelObject::FIELDSET_DEFAULT])
                ->getModelObject();

            // Check module and perm
            $this->getModuleAndCheckPerm($configuration->feature, 'read');

            $this->configurations[] = [
                $configuration->feature => $this->getConf($configuration->feature, $data['context']),
            ];
        }

        $collection = new Collection($this->configurations);
        $collection->setType(CConfiguration::RESOURCE_TYPE);

        return $this->renderApiResponse($collection);
    }

    /**
     * @throws Exception|ApiException
     * @api
     */
    public function setConfigurations(RequestApi $request_api): Response
    {
        // Get request body data
        $data     = $this->getParamsAndLoad($request_api);
        $strategy = CConfigurationModelManager::getStrategy();

        /** @var CConfiguration $_config */
        foreach ($request_api->getModelObjectCollection(CConfiguration::class) as $_config) {
            // Check module and perm
            $this->getModuleAndCheckPerm($_config->feature, 'admin');

            // Check if config is existant before updating it
            $this->getConf($_config->feature, $data['context']);

            try {
                if (!$data['context']) {
                    if (
                        $msg = CAppUI::setConf(
                            $_config->feature,
                            $_config->value
                        )
                    ) {
                        throw new Exception($msg);
                    }
                } elseif (
                    $msg = CConfiguration::setConfig(
                        $_config->feature,
                        $_config->value,
                        $data['object'],
                        $strategy,
                        ($data['context'] === 'static') ? '1' : '0'
                    )
                ) {
                    throw new Exception($msg);
                }
            } catch (Exception $e) {
                throw new ApiException(
                    CAppUI::tr('CConfiguration-error-Error when setting configuration %s', $_config->feature)
                );
            }

            // If configuration correctly setted, add it in array of created configs
            // getConf() returned value is probably not the actual value in cache so return setted value
            $this->configurations[] = [
                "feature" => $_config->feature,
                "value"   => $_config->value,
            ];
        }

        $resource = new Collection($this->configurations);
        $resource->setType(CConfiguration::RESOURCE_TYPE);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return array
     * @throws ApiException
     * @throws Exception
     */
    private function getParamsAndLoad(RequestApi $request_api): array
    {
        $context = $request_api->getRequest()->query->get('context');
        $object  = CStoredObject::loadFromGuid($context);

        if (CStoredObject::isGuid($context) && (!$object || !$object->_id)) {
            throw new ApiException(CAppUI::tr('CConfiguration-error-%s is not a valid object', $context));
        }

        return [
            "context" => $context,
            "object"  => $object,
        ];
    }

    /**
     * @throws ApiException|AccessDeniedException
     */
    private function getModuleAndCheckPerm(string $feature, string $perm): void
    {
        $parts = explode(' ', $feature, 2);
        if (!is_array($parts)) {
            throw new ApiException(CAppUI::tr('CConfiguration-error-Not a valid feature name'));
        }
        $mod_name       = $parts[0];
        $this->mod_name = $this->getActiveModule($mod_name);

        $module   = CModule::getActive($this->mod_name);
        $has_perm = false;
        switch ($perm) {
            case 'read':
                if (!$this->checkPermRead($module)) {
                    $has_perm = true;
                }
                break;
            case 'admin':
                if (!$this->checkPermAdmin($module)) {
                    $has_perm = true;
                }
                break;
            default:
                throw new ApiException('Permission is mandatory');
        }

        if ($has_perm) {
            throw new AccessDeniedException("Cannot access module '{$mod_name}'");
        }
    }

    /**
     * @return mixed
     * @throws ApiException
     * @throws Exception
     */
    private function getConf(string $feature, ?string $context)
    {
        $feature_parts = explode(' ', $feature, 2);

        // Not a valid feature name (module_name lorem ipsum)
        if (!is_array($feature_parts)) {
            throw new ApiException(CAppUI::tr('CConfiguration-error-Not a valid feature name'));
        }

        // Get config -  CAppUI::conf() will returned null if feature is inexistant
        $configuration = CAppUI::conf($feature, $context);
        if ($configuration === null) {
            throw new ApiException(CAppUI::tr('CConfiguration-error-Error when getting configuration %s', $feature));
        }

        return $configuration;
    }

    private function getInstanceConfigurations(RequestApi $request_api): array
    {
        try {
            // CAppUI::conf already has it's own cache
            $flattened_configs = CMbArray::flattenArrayKeys(CAppUI::conf($this->mod_name), $this->mod_name);
        } catch (Throwable $e) {
            $flattened_configs = [];
        }

        return $this->applyFilter($request_api, $flattened_configs);
    }

    private function getStaticConfigurations(
        RequestApi $request_api
    ): array {
        try {
            // Cache handled by ConfigurationManager
            $configs = (ConfigurationManager::get())->getValuesForModule($this->mod_name);
        } catch (Throwable $e) {
            $configs = [];
        }

        $flattened_configs = CMbArray::flattenArrayKeys($configs, $this->mod_name);

        return $this->applyFilter($request_api, $flattened_configs);
    }

    /**
     * @throws Exception
     */
    private function getContextualConfigurations(
        RequestApi $request_api
    ): array {
        $configurations = [];

        $this->model              = CConfiguration::getModel($this->mod_name);
        $this->available_contexts = array_keys($this->model);

        $configurations = $this->buildGroupsConfigurations($request_api, $configurations);

        return $this->buildOtherContextualConfigurations($request_api, $configurations);
    }

    /**
     * @throws CMbException
     */
    private function getContextualConfigurationForContext(
        RequestApi $request_api,
        CStoredObject $context,
        string $context_class = null
    ): array {
        $configs         = [];
        $context_configs = CConfigurationModelManager::getValues($this->mod_name, $context->_class, $context->_id);

        if (!$context_class) {
            $context_class = $context->_class;
        }

        foreach (CMbArray::flattenArrayKeys($context_configs) as $_config => $_value) {
            $_key = $this->mod_name . ' ' . trim($_config);
            if (isset($this->model[$context_class][$_key])) {
                $configs[$_key] = $_value;
            }
        }

        return $this->applyFilter($request_api, $configs);
    }

    private function isSubContext(
        string $context
    ): bool {
        return str_contains($context, ' ');
    }

    /**
     * @throws CMbException
     */
    private function buildGroupsConfigurations(
        RequestApi $request_api,
        array $configurations = []
    ): array {
        $group = $request_api->getGroup();

        if (in_array('CGroups', $this->available_contexts, true)) {
            $configurations[$group->_class] = [
                $group->_id => $this->getContextualConfigurationForContext($request_api, $group),
            ];

            // For each subcontext load objects from current group
            foreach ($this->available_contexts as $_ctx) {
                // TODO Handle constantes configurations
                if (!$this->isSubContext($_ctx) || (strpos($_ctx, 'constantes') === 0)) {
                    continue;
                }

                $configurations = $this->addContextualConfigurationsForSubContexts(
                    $request_api,
                    $group,
                    $_ctx,
                    $configurations
                );
            }
        }

        return $configurations;
    }

    /**
     * @throws Exception
     */
    private function buildOtherContextualConfigurations(
        RequestApi $request_api,
        array $configurations = []
    ): array {
        foreach ($this->available_contexts as $_context_class) {
            if (($_context_class === 'CGroups') || $this->isSubContext($_context_class)) {
                continue;
            }

            /** @var CStoredObject $ctx_instance */
            $ctx_instance  = new $_context_class();
            $all_instances = $ctx_instance->loadMatchingListEsc();

            foreach ($all_instances as $_context) {
                if (!$_context->getPerm(PERM_READ)) {
                    continue;
                }

                if (!isset($configurations[$_context_class])) {
                    $configurations[$_context_class] = [];
                }

                $configurations[$_context_class][$_context->_id]
                    = $this->getContextualConfigurationForContext($request_api, $_context);
            }
        }

        return $configurations;
    }

    /**
     * @throws CMbException
     */
    private function addContextualConfigurationsForSubContexts(
        RequestApi $request_api,
        CGroups $group,
        string $ctx,
        array $configurations
    ): array {
        $ctx_objects = $this->loadSubCtxObjects($group, $ctx);

        /** @var CStoredObject $_object */
        foreach ($ctx_objects as $_object) {
            if (!$_object->getPerm(PERM_READ)) {
                continue;
            }

            if (!isset($configurations[$_object->_class])) {
                $configurations[$_object->_class] = [];
            }

            $configurations[$_object->_class][$_object->_id]
                = $this->getContextualConfigurationForContext($request_api, $_object, $ctx);
        }

        return $configurations;
    }

    /**
     * Only handle sub ctx from CGroups
     * @throws Exception
     */
    private function loadSubCtxObjects(
        CGroups $group,
        string $ctx_string
    ): array {
        [$ctx_class,] = explode(' ', $ctx_string);

        /** @var CStoredObject $obj */
        $obj           = new $ctx_class();
        $obj->group_id = $group->_id;

        return $obj->loadMatchingListEsc();
    }
}
