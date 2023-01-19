<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Profiles;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;

trait ProfileResourceTrait
{
    private array $interaction_names = [];

    /**
     * @inheritDoc
     */
    protected function makeExplicitCategories(array $canonicals, array $transactions = []): array
    {
        $categories = [];

        $class_map = new FHIRClassMap();
        foreach ($canonicals as $canonical) {
            if (!$transactions) {
                if ($resource = $class_map->resource->getResource($canonical)) {
                    $transactions = $resource->getInteractions();
                }

                $categories[$canonical] = $transactions ?? [];
            } else {
                foreach ($transactions as $resource_canonical => $resource_transactions) {
                    if (is_string($resource_canonical) && $resource_canonical === $canonical) {
                        $categories[$canonical] = $resource_transactions;
                    } elseif (!is_string($resource_canonical)) {
                        $categories[$canonical] = $transactions;
                    }
                }
            }
        }

        return $categories;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getEvenementsFromCategories(): array
    {
        if ($this->interaction_names) {
            return $this->interaction_names;
        }

        $interaction_names = array_unique(CMbArray::array_flatten($this->_categories));

        $interactions = [];
        foreach ($interaction_names as $name) {
            $interaction = CFHIRInteraction::getFromName($name);
            if ($interaction && ($interaction_class = CClassMap::getInstance()->getShortName($interaction))) {
                $interactions[$interaction::NAME] = $interaction_class;
            }
        }

        return $this->interaction_names = $interactions;
    }
}
