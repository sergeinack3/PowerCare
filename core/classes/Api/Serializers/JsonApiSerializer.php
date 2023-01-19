<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Serializers;

use DateTime;
use DateTimeZone;
use Exception;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Transformers\ModelObjectTransformer;

class JsonApiSerializer extends AbstractSerializer
{
    public const ID_KEYWORD            = 'id';
    public const TYPE_KEYWORD          = 'type';
    public const RELATION_NAME_KEYWORD = 'relation';

    /** @var array */
    private $included = [];

    /**
     * @inheritDoc
     */
    public function serialize(): array
    {
        $resource = $this->resource;

        // Transform item to data
        $datas_transformed = $resource->transform();

        // Convert to json:api specifications
        if ($this->resource instanceof Item) {
            $datas_converted = $this->convertToJsonApi($datas_transformed);
            $datas_optimized = $this->optimizeRelationships($datas_converted);
        } else {
            $datas_converted = [];
            foreach ($datas_transformed as $key => $datas_to_convert) {
                $datas_converted[] = $this->convertToJsonApi($datas_to_convert);
            }

            $datas_optimized = [];
            foreach ($datas_converted as $key => $datas_to_optimize) {
                $datas_optimized[] = $this->optimizeRelationships($datas_to_optimize);
            }
        }

        // Final document
        $document = [];

        // Data (must)
        $document['data'] = $datas_optimized;

        // Meta (may)
        $document['meta'] = ($resource instanceof Collection)
            ? array_merge($this->getDefaultMeta(), $resource->getMetas())
            : $this->getDefaultMeta();

        // Links (may)
        if (!empty($resource->getLinks()) && $this->resource instanceof Collection) {
            $document['links'] = $resource->getLinks();
        }

        // Includes (may)
        if (!empty($this->included)) {
            $document['included'] = array_values($this->included);
        }

        return $document;
    }

    private function getDefaultMeta(): array
    {
        try {
            $dt   = new DateTime('now', new DateTimeZone('Europe/Paris'));
            $date = $dt->format('Y-m-d H:i:sP');
        } catch (Exception $exception) {
            $date = null;
        }

        return [
            'date' => $date,
            'copyright' => 'OpenXtrem-' . date('Y'),
            'authors' => 'dev@openxtrem.com'
        ];
    }

    /**
     * Convert datas transformed to json:api
     */
    private function convertToJsonApi(array $datas_to_convert): array
    {
        $data_converted = [];

        // type
        $data_converted[static::TYPE_KEYWORD] = $datas_to_convert['datas']['_type'];
        unset($datas_to_convert['datas']['_type']);

        // id
        $data_converted[static::ID_KEYWORD] = $datas_to_convert['datas']['_id'];
        unset($datas_to_convert['datas']['_id']);

        // relation
        if (isset($datas_to_convert['datas'][ModelObjectTransformer::RELATION_NAME_KEYWORD])) {
            $data_converted[static::RELATION_NAME_KEYWORD] =
                $datas_to_convert['datas'][ModelObjectTransformer::RELATION_NAME_KEYWORD];
            unset($datas_to_convert['datas'][ModelObjectTransformer::RELATION_NAME_KEYWORD]);
        }

        // attributes
        $data_converted['attributes'] = $datas_to_convert['datas'];

        // relationships
        if (array_key_exists('relationships', $datas_to_convert)) {
            $data_converted['relationships'] = [];
            foreach ($datas_to_convert['relationships'] as $relation_name => $relations) {
                if (isset($relations['datas'])) {
                    // Case of to-one relation with non null value.
                    $data_converted['relationships'][$relation_name] = $this->convertToJsonApi($relations);
                } elseif ($relations === null) {
                    // Case of to-one relation with null value.
                    $data_converted['relationships'][$relation_name] = null;
                } else {
                    // Case of to-many relation.
                    // Init the relation_name to have an empty array if $relations is an empty array.
                    $data_converted['relationships'][$relation_name] = [];

                    foreach ($relations as $relation) {
                        $data_converted['relationships'][$relation_name][] = $this->convertToJsonApi($relation);
                    }
                }
            }
        }

        // links
        if (array_key_exists('links', $datas_to_convert)) {
            $data_converted['links'] = $datas_to_convert['links'];
        }

        // object meta
        if (array_key_exists('meta', $datas_to_convert)) {
            $data_converted['meta'] = $datas_to_convert['meta'];
        }

        return $data_converted;
    }

    /**
     * Optimize relations datas and create included datas
     *
     * @param array $datas_to_optimize
     *
     * @return array data optimized
     */
    private function optimizeRelationships(array $datas_to_optimize): array
    {
        if (!array_key_exists('relationships', $datas_to_optimize)) {
            return $datas_to_optimize;
        }

        // Reasign datas
        $datas_optimized                  = $datas_to_optimize;
        $datas_optimized['relationships'] = [];

        foreach ($datas_to_optimize['relationships'] as $relation_name => $relations) {
            // to-one relation
            if (isset($relations[static::TYPE_KEYWORD])) {
                $relation_type = $relations[static::TYPE_KEYWORD];
                $relation_id   = $relations[static::ID_KEYWORD];

                $datas_optimized['relationships'][$relation_name]['data'] = [
                    static::TYPE_KEYWORD => $relations[static::TYPE_KEYWORD],
                    static::ID_KEYWORD   => $relations[static::ID_KEYWORD],
                ];

                // Create includes
                $this->included[$relation_type . '_' . $relation_id] = $relations;
            } elseif ($relations === null) {
                $datas_optimized['relationships'][$relation_name] = null;
            } else {
                $datas_optimized['relationships'][$relation_name] = [];

                // to-many relation
                foreach ($relations as $relation) {
                    $relation_type = $relation[static::TYPE_KEYWORD];
                    $relation_id   = $relation[static::ID_KEYWORD];

                    $datas_optimized['relationships'][$relation_name]['data'][] = [
                        static::TYPE_KEYWORD => $relation_type,
                        static::ID_KEYWORD   => $relation_id,
                    ];

                    // Create includes
                    $this->included[$relation_type . '_' . $relation_id] = $relation;
                }
            }
        }

        return $datas_optimized;
    }
}
