<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Resources;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\CModelObject;

class Item extends AbstractResource
{
    /** @var array */
    private $additional_datas = [];

    /** @var AbstractResource[] */
    protected $additionnal_relations = [];

    /**
     * Item constructor.
     *
     * @param array|object $datas
     *
     * @throws ApiException
     */
    public function __construct($datas, bool $default_metas = true)
    {
        $model_class = is_object($datas) ? get_class($datas) : null;
        parent::__construct($datas, $model_class, $default_metas);
    }

    /**
     * @inheritDoc
     */
    public function transform(): array
    {
        $datas_transformed = $this->createTransformer()->createDatas();

        // additional datas
        $datas_transformed['datas'] = array_merge($datas_transformed['datas'], $this->additional_datas);

        if (!empty($this->additionnal_relations)) {
            $datas_transformed['relationships'] = $datas_transformed['relationships'] ?? [];

            foreach ($this->additionnal_relations as $relation_name => $relation) {
                $transformed_relation = $relation->transform();

                if (is_int($relation_name)) {
                    $relation_object = $relation->getDatas();
                    if ($relation_object instanceof CModelObject) {
                        $relation_name = $relation_object::RESOURCE_TYPE;
                    }
                }

                if ($relation instanceof Item) {
                    $datas_transformed['relationships'][$relation_name][] = $transformed_relation;
                } elseif (is_array($transformed_relation)) {
                    foreach ($transformed_relation as $rel) {
                        $datas_transformed['relationships'][$relation_name][] = $rel;
                    }
                }
            }
        }

        return $this->datas_transformed = $datas_transformed;
    }

    /**
     * @param array $datas
     *
     * @return Item
     * @throws ApiException
     */
    public function addAdditionalDatas(array $datas): Item
    {
        $this->additional_datas = array_merge($this->additional_datas, $datas);

        return $this;
    }

    public function addAdditionalRelation(AbstractResource $resource, string $relation_name = null): self
    {
        if ($relation_name) {
            $this->additionnal_relations[$relation_name] = $resource;
        } else {
            $this->additionnal_relations[] = $resource;
        }

        return $this;
    }

    /**
     * @param array $resources This array can be key/value with the keys the names of the relations to add
     */
    public function addAdditionalRelations(array $resources): self
    {
        foreach ($resources as $relation_name => $resource) {
            if ($resource instanceof AbstractResource) {
                $this->addAdditionalRelation($resource, is_int($relation_name) ? null : $relation_name);
            }
        }

        return $this;
    }

    public function getAdditionnalRelations(): array
    {
        return $this->additionnal_relations;
    }
}
