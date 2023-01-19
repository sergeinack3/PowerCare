<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleRequest;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotFound;
use Ox\Interop\Fhir\Exception\CFHIRExceptionRequired;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Bundle\CFHIRResourceBundle;
use Ox\Interop\Fhir\Utilities\BundleBuilder\BundleBuilder;
use Ox\Mediboard\System\CUserLog;

/**
 * The history interaction retrieves the history of either a particular resource, all resources of a given type, or all
 * resources supported by the system
 */
class CFHIRInteractionHistory extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "history-instance";

    /** @var string */
    private $version_id;

    /** @var string */
    private $resource_id;

    /**
     * @param string $resource_id
     *
     * @return CFHIRInteractionHistory
     */
    public function setResourceId(?string $resource_id): self
    {
        $this->resource_id = $resource_id;

        return $this;
    }

    /**
     * @param string $version_id
     *
     * @return CFHIRInteractionHistory
     */
    public function setVersionId(?string $version_id): self
    {
        $this->version_id = $version_id;

        return $this;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function handleResult(CFHIRResource $resource, $result): CFHIRResponse
    {
        $resource_id   = $resource->getResourceId();
        $resource_type = $resource->getResourceType();
        if ($result === null) {
            throw new CFHIRExceptionNotFound(
                "Could not find " . $resource_type . " #" . $resource_id
            );
        }

        if (!$result instanceof CStoredObject || !$result->_history) {
            throw new CFHIRExceptionNotFound("No history " . $resource_type . " #$resource_id");
        }

        $parts  = explode("?", urldecode(CMbArray::get($_SERVER, "REQUEST_URI")), 2);
        $params = null;
        if (count($parts) > 1) {
            $params = $parts[1];
        }
        $route_params = ['resource'    => $resource_type];
        if ($this->resource_id && $this->version_id) {
            $route_name   = 'fhir_history_id_version';
            $route_params = array_merge(
                $route_params,
                ['resource_id' => $this->resource_id, 'version_id'  => $this->version_id]
            );
        } elseif ($this->resource_id) {
            $route_name   = 'fhir_history_id';
            $route_params = array_merge($route_params, ['resource_id' => $this->resource_id]);
        } else {
            $route_name   = 'fhir_history';
        }
        $root = CFHIRController::getUrl($route_name, $route_params);
        $url  = $root . ($params ? "?$params" : "");

        $bundle_builder = BundleBuilder::getBuilderHistory($resource->buildFrom(new CFHIRResourceBundle()))
            ->setTotal(count($result->_history))
            ->addLink($url, 'self');

        $objects = $this->version_id ? [$this->version_id => $result] : $result->loadListByHistory();
        foreach ($objects as $object_history_key => $object_history) {
            // resource
            $history_resource = $resource->buildSelf();
            $history_resource->mapFrom($object_history);

            // Récupérer le log pour le method de la request
            $user_log = new CUserLog();
            $user_log->load($object_history_key);
            if (!$user_log->_id) {
                continue;
            }

            $full_url = CFHIRController::getUrl(
                "fhir_history_id_version",
                [
                    'resource'    => $history_resource->getResourceType(),
                    'resource_id' => $history_resource->getResourceId(),
                    'version_id'  => $object_history_key,
                ]
            );

            // request
            $request = (new CFHIRDataTypeBundleRequest())
                ->setMethod($this->mapMethodLog($user_log->type))
                ->setUrl($full_url);

            // entry
            $bundle_builder->addEntry()
                ->setFullUrl($full_url)
                ->setRequestElement($request)
                ->setResourceElement(new CFHIRDataTypeResource($history_resource));
        }

        $this->setResource($bundle_builder->build());

        return new CFHIRResponse($this, $this->format);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function mapMethodLog(string $type): string
    {
        switch ($type) {
            case "delete":
                $method = "DELETE";
                break;
            case "create":
                $method = "POST";
                break;
            default:
                $method = "PUT";
                break;
        }

        return $method;
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        if (!$this->resource_id) {
            $interaction_name = $this::NAME;
            throw new CFHIRExceptionRequired("Element 'resource_id' is missing in interaction '$interaction_name'");
        }

        if (!$this->version_id) {
            return $this->resourceType . '/' . $this->resource_id . "/_history";
        }

        return $this->resourceType . '/' . $this->resource_id . "/_history/" . $this->version_id;
    }
}
