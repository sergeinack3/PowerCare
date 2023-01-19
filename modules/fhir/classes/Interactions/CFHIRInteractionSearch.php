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
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Bundle\CFHIRResourceBundle;
use Ox\Interop\Fhir\Utilities\BundleBuilder\BundleBuilder;

/**
 * This interaction searches a set of resources based on some filter criteria
 */
class CFHIRInteractionSearch extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "search-type";

    /** @var string Resource ID */
    public $resource_id;

    /**
     * @inheritdoc
     */
    public function handleResult(CFHIRResource $resource, $result): CFHIRResponse
    {
        $params  = ($params = $resource->getParametersBrut()) ? http_build_query($params) : null;
        $root    = CFHIRController::getUrl("fhir_search", ['resource' => $resource->getResourceType()]);
        $fullUrl = $root . ($params ? "?$params" : '');

        $bundle_builder = BundleBuilder::getBuilderSearchset($this->resource->buildFrom(new CFHIRResourceBundle()))
            ->addLink($fullUrl, 'self')
            ->setTotal($result["total"] ?? 0);

        if (!$result) {
            $this->setResource($bundle_builder->build());

            return new CFHIRResponse($this, $this->format);
        }

        // manage links
        $parsed_params = CFHIR::parseQueryString($params, true);
        if (CMbArray::get($result, "paginate")) {
            // relation = next
            if (CMbArray::get($result, "offset") < CMbArray::get($result, "total")) {
                $next_params            = $parsed_params;
                $next_params["_offset"] = [$result["offset"] + $result["step"]];
                $bundle_builder->addLink($root . "?" . CFHIR::makeQueryString($next_params), 'next');

                // relation last
                $next_params            = $parsed_params;
                $nb_step                = ceil(CMbArray::get($result, "total") / CMbArray::get($result, "step"));
                $next_params["_offset"] = [$nb_step * CMbArray::get($result, "step")];
                $bundle_builder->addLink($root . "?" . CFHIR::makeQueryString($next_params), 'last');
            }

            // relation = previous
            if (CMbArray::get($result, "offset") > CMbArray::get($result, "step")) {
                $prev_params            = $parsed_params;
                $prev_params["_offset"] = [$result["offset"] - $result["step"]];

                $bundle_builder->addLink($root . "?" . CFHIR::makeQueryString($prev_params), 'previous');

                // relation = first
                $prev_params = $parsed_params;
                unset($prev_params["_offset"]);
                $link = $root . ($prev_params ? "?" . CFHIR::makeQueryString($prev_params) : '');
                $bundle_builder->addLink($link, 'first');
            }
        }

        /** @var CStoredObject $object */
        foreach (CMbArray::get($result, "list") as $object) {
            // add entry resource in bundle
            $bundle_builder->addResource(
                $resource->buildSelf()
                    ->mapFrom($object)
            );
        }

        // build resource bundle
        $this->setResource($bundle_builder->build());

        return new CFHIRResponse($this, $this->format);
    }
}
