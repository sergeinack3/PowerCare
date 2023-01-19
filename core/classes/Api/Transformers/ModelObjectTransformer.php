<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Transformers;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Resources\AbstractResource;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Symfony\Component\Routing\RouterInterface;

class ModelObjectTransformer extends AbstractTransformer
{
    public const RELATION_NAME_KEYWORD = '_relation';

    /**
     * @return array
     * @throws ApiException
     */
    public function createDatas(): array
    {
        /** @var CModelObject $model */
        $model      = $this->item->getDatas();
        $this->type = $this->item->getType() ?? $model::RESOURCE_TYPE;
        $this->id   = $model->_id;
        if ($router = $this->item->getRouter()) {

            if ($self = $this->createSelfLinkForResource($model, $router)) {
                $this->links['self'] = $self;
            }

            $this->links['schema'] = $model->getApiSchemaLink($router, $this->item->getFieldsetsByRelation());

            if (
                $model->_spec->loggable
                && $model->_spec->loggable !== CMbObjectSpec::LOGGABLE_NEVER
                && ($history_link = $model->getApiHistoryLink($router))
            ) {
                $this->links['history'] = $history_link;
            }
        }

        if ($this->item->getWithPermissions() && $model instanceof CStoredObject) {
            $this->meta[AbstractResource::PERMISSIONS_KEYWORD] = [
                'perm' => ($model->getPerm(PERM_EDIT)) ? 'edit' : (($model->getPerm(PERM_READ)) ? 'read' : 'denied'),
            ];
        }

        $this->links = array_merge($this->links, $this->item->getLinks());

        // Default fieldset for item
        if ($this->item->getFieldsetsByRelation() === null) {
            $this->item->addModelFieldset([$model::FIELDSET_DEFAULT]);
        }

        $mapping = $model->getFieldsSpecsByFieldsets($this->item->getFieldsetsByRelation());
        foreach ($mapping as $field_name => $spec) {
            // Access data
            $field_value = $model->$field_name === '' ? null : $model->$field_name;

            $this->attributes[$field_name] = $this->convertType($field_value, $spec->getPHPSpec());
        }

        if ($meta = $this->item->getMetas()) {
            $this->meta = $meta;
        }

        // Relationships
        if ($this->item->getRecursionDepth() >= self::RECURSION_LIMIT) {
            return $this->render();
        }

        // Default relations
        if ($this->item->getModelRelations() === null) {
            $this->item->setModelRelations($model::RELATIONS_DEFAULT);
        }

        foreach ($this->item->getModelRelations() as $relation_name) {
            $this->addRelationship($relation_name, $model);
        }

        return $this->render();
    }

    /**
     * @throws ApiException
     */
    private function addRelationship(string $relation_name, CModelObject $model): void
    {
        // Naming convention
        $method_name = 'getResource' . ucfirst($relation_name);

        if (!method_exists($model, $method_name)) {
            throw new ApiException("Invalid method name '{$method_name}' in class '{$model->_class}'");
        }

        /** @var AbstractResource|null|array $resource */
        $resource = $model->$method_name();

        // The getResourceXXX methods MUST always return an AbstractResource, null or an empty array.
        // If the relation is to-one and is empty null must be returned.
        // If the relation is a to-many and is empty and empty array must be returned.
        if (!$this->isValidResource($resource)) {
            throw new ApiException("Invalid resource returned in class '{$model->_class}::{$method_name}'");
        }

        if ($resource instanceof AbstractResource) {
            // Set recursion depth limit
            $resource->setRecursionDepth($this->item->getRecursionDepth() + 1);

            // Set fieldsets on relations
            if ($resource->isModelObjectResource()) {
                $resource->setModelFieldsets($this->item->getFieldsetsByRelation($relation_name) ?? []);
            }
        }

        // Actually add the resource to $this->relationships.
        if ($resource === null) {
            // Empty to-one relation.
            $this->relationships[$relation_name] = null;
        } elseif ($resource === []) {
            // Empty to-many relation.
            $this->relationships[$relation_name] = [];
        } elseif ($resource instanceof Item) {
            // Non empty to-one relation. Item, only one by relation_name per object
            $this->relationships[$relation_name] = $resource->transform();
        } else {
            // Non empty to-many relation.
            // Initialize the relationship.
            $this->relationships[$relation_name] = [];

            // Collection, multiple by relation_name per object
            $relation_datas = $resource->transform();
            foreach ($relation_datas as $relation_data) {
                $this->relationships[$relation_name][] = $relation_data;
            }
        }
    }

    /**
     * A valid resource is either null, an empty array or an AbstractResource.
     *
     * @param mixed $resource
     */
    private function isValidResource($resource): bool
    {
        if ($resource === null) {
            return true;
        }

        if (is_array($resource) && empty($resource)) {
            return true;
        }

        return $resource instanceof AbstractResource;
    }

    /**
     * Convert the internal application type to an external type (string, bool, int, float).
     *
     * @param mixed        $field_value
     * @param CMbFieldSpec $spec
     *
     * @return mixed
     */
    private function convertType($field_value, string $spec)
    {
        if ($field_value !== null) {
            switch ($spec) {
                case CMbFieldSpec::PHP_TYPE_STRING:
                    // Do not touch strings
                    break;
                case CMbFieldSpec::PHP_TYPE_BOOL:
                case CMbFieldSpec::PHP_TYPE_INT:
                case CMbFieldSpec::PHP_TYPE_FLOAT:
                default:
                    // Force data type
                    settype($field_value, $spec);
            }
        }

        return $field_value;
    }

    /**
     * Generate the self link from the object.
     * Add the relations, relations_excluded and fieldsets passed in the request parameters.
     */
    private function createSelfLinkForResource(CModelObject $object, RouterInterface $router): ?string
    {
        if ($link = $object->getApiLink($router)) {
            $args_presents = str_contains($link, '?');
            if ($relations = $this->item->getRequestRelations()) {
                $link .= ($args_presents ? '&' : '?') . RequestRelations::QUERY_KEYWORD_INCLUDE . '=' . $relations;
                $args_presents = true;
            }

            if ($exluded_relations = $this->item->getRequestExcludedRelations()) {
                $link .= ($args_presents ? '&' : '?') . RequestRelations::QUERY_KEYWORD_EXCLUDE
                    . '=' . $exluded_relations;
                $args_presents = true;
            }

            if ($fieldsets = $this->item->getRequestFieldsets()) {
                $link .= ($args_presents ? '&' : '?') . RequestFieldsets::QUERY_KEYWORD . '=' . $fieldsets;
            }
        }

        return $link;
    }
}
