<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities;

use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeComplex;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Description
 */
class CFHIRTools
{
    /**
     * @param CFHIRResource|CFHIRDataType $resource
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getNonEmptyFields($resource): array
    {
        $is_object   = is_object($resource);
        $is_resource = $is_object && is_subclass_of($resource, CFHIRResource::class);
        $is_datatype = $is_object && !$is_resource && is_subclass_of($resource, CFHIRDataType::class);
        if (!$is_resource && !$is_datatype) {
            return [];
        }

        $fields = $resource->isSummary()
            ? CFHIRDefinition::getSummariesFields($resource)
            : CFHIRDefinition::getFields($resource);
        $data   = [];
        foreach ($fields as $field) {
            $method = "get" . ucfirst($field);
            if (method_exists($resource, $method) && $resource instanceof CFHIRResource) {
                $v = $resource->$method();
            } elseif (!property_exists($resource, $field)) {
                continue;
            } else {
                /** @var CFHIRDataType $v */
                $v = $resource->{$field};
            }

            if (is_array($v)) {
                $v = array_filter($v, function (CFHIRDataType $datatype) {
                    return !$datatype->isNull();
                });
                if (empty($v)) {
                    continue;
                }
            } elseif (!$v instanceof CFHIRDataType || $v->isNull()) {
                    continue;
            }

            $data[$field] = $v;
        }

        // transform datatype choice
        $ordered_data = [];
        foreach ($data as $key => $item) {
            $definition_field = CFHIRDefinition::getElementDefinition($resource, $key);
            if (($definition_field['datatype']['class'] ?? null) === CFHIRDataTypeChoice::class) {
                unset($data[$key]);

                // keep legacy treatment
                if ($item instanceof CFHIRDataTypeChoice) {
                    $item = $item->getValue();
                }
                $key  = $key . $item::NAME;
            }

            $ordered_data[$key] = $item;
        }

        return $ordered_data;
    }

    /**
     * @param array  $datatypes
     * @param string $field
     *
     * @return array
     */
    public static function manageDatatypeJSONArray(array $datatypes, string $field): array
    {
        $values = [];
        /** @var CFHIRDataType $datatype */
        foreach ($datatypes as $datatype) {
            // item is complex type
            $items = $datatype->toJSON($field);
            if ($datatype instanceof CFHIRDataTypeComplex && !$datatype instanceof CFHIRDataTypeExtension) {
                $values[$field][] = $items[$field] ?? null;
            } else {
                $values[$field][]    = $items[$field] ?? null;
                $values["_$field"][] = $items["_$field"] ?? null;
            }
        }

        if (count(array_filter($values["_$field"] ?? [])) === 0) {
            unset($values["_$field"]);
        }

        if (count(array_filter($values[$field] ?? [])) === 0) {
            unset($values[$field]);
        }

        return $values;
    }
}
