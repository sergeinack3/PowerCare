<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Resources;

use DateTime;
use DateTimeZone;
use Exception;
use JsonSerializable;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Serializers\AbstractSerializer;
use Ox\Core\Api\Serializers\JsonApiSerializer;
use Ox\Core\Api\Transformers\AbstractTransformer;
use Ox\Core\Api\Transformers\ArrayTransformer;
use Ox\Core\Api\Transformers\ModelObjectTransformer;
use Ox\Core\Api\Transformers\ObjectTransformer;
use Ox\Core\CMbArray;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use ReturnTypeWillChange;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractResource implements JsonSerializable
{
    /** @var string */
    public const CURRENT_RELATION_NAME = 'current';

    /** @var string */
    public const SCHEMA_KEYWORD = 'schema';

    /** @var string */
    public const PERMISSIONS_KEYWORD = 'permissions';

    /** @var string */
    protected $type;

    /** @var array */
    protected $datas;

    /** @var array */
    protected $datas_transformed;

    /** @var string */
    protected $name;

    /** @var string */
    protected $model_class;

    /** @var string[][] */
    protected $model_fieldsets;

    /** @var string[] */
    protected $model_relations;

    /** Relations present in the request URL */
    protected string $request_relations = '';

    /** Excluded relations present in the request URL */
    protected string $request_excluded_relations = '';

    /** Fieldsets present in the request URL */
    protected string $request_fieldsets = '';

    /** @var string */
    protected $format;

    /** @var array */
    protected $links = [];

    /** @var array */
    protected $metas = [];

    /** @var string */
    protected $request_url;

    /** @var string */
    protected $serializer = JsonApiSerializer::class;

    /** @var RouterInterface */
    protected $router;

    /** @var int */
    protected $recursion_depth = 0;

    /** @var bool */
    protected $with_permissions = false;

    /**
     * @param RequestApi   $request
     * @param array|object $datas
     *
     * @return static
     * @throws ApiException
     */
    final public static function createFromRequest(RequestApi $request, $datas)
    {
        $instance_class = static::class;

        $instance = new $instance_class($datas);
        if ($instance->isModelObjectResource()) {
            // relations from which we remove excluded relations
            $request_relations  = $request->getRelations();
            $excluded_relations = $request->getRelationsExcluded();

            if (!empty($request_relations)) {
                $instance->request_relations = implode(RequestRelations::RELATION_SEPARATOR, $request_relations);
                $instance->request_excluded_relations = empty($excluded_relations)
                    ? ''
                    : implode(RequestRelations::RELATION_SEPARATOR, $excluded_relations);

                $request_relations = count($request_relations) === 1 ? reset($request_relations) : $request_relations;
                $instance->setModelRelations($request_relations, $excluded_relations);
            }

            // fieldsets
            $request_fieldsets = $request->getFieldsets();
            if (!empty($request_fieldsets)) {

                $instance->request_fieldsets = implode(RequestFieldsets::FIELDSETS_SEPARATOR, $request_fieldsets);

                $request_filedsets = count($request_fieldsets) === 1 ? reset($request_fieldsets) : $request_fieldsets;
                $instance->setModelFieldsets($request_filedsets);
            }

            // include schema
            if ($request->getRequest()->query->getBoolean(self::SCHEMA_KEYWORD)) {
                $instance->addMetasSchema();
            }

            // include top level permission
            if ($request->getRequest()->query->getBoolean(self::PERMISSIONS_KEYWORD)) {
                $instance->with_permissions = true;
            }
        }
        $instance->setFormat($request->getFormatsExpected());
        $instance->setRequestUrl($request->getUri());

        return $instance;
    }

    /**
     * AbstractResource constructor.
     *
     * @param mixed       $datas
     * @param null|string $model_class
     * @param bool        $default_meta Add default meta to the resource.
     *
     * @throws ApiException
     */
    protected function __construct($datas, string $model_class = null, bool $default_meta = true)
    {
        if (!is_array($datas) && !is_object($datas)) {
            throw new ApiException('Resource datas must be an array or an object');
        }

        $this->datas       = $datas;
        $this->model_class = $model_class;

        if ($default_meta) {
            $this->setDefaultMetas();
        }
    }

    /**
     * @return array
     */
    abstract public function transform(): array;

    /**
     * @return AbstractTransformer
     */
    protected function createTransformer(): AbstractTransformer
    {
        if (!$this->model_class) {
            $transformer = ArrayTransformer::class;
        } elseif (is_subclass_of($this->model_class, CModelObject::class)) {
            $transformer = ModelObjectTransformer::class;
        } else {
            $transformer = ObjectTransformer::class;
        }

        return new $transformer($this);
    }

    /**
     * @param string $format
     *
     * @return AbstractResource
     * @throws ApiException
     */
    public function setFormat(string $format): AbstractResource
    {
        if (!in_array($format, RequestFormats::FORMATS, true)) {
            throw new ApiException('Invalid resource format, use RequestFormats contantes.');
        }
        $this->format = $format;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return AbstractResource
     */
    public function setRequestUrl(string $url): AbstractResource
    {
        $this->request_url = $url;

        return $this;
    }

    /**
     * Groups of fileds
     *
     * @param array|string $fieldsets
     *
     * @return AbstractResource
     * @throws ApiException
     */
    public function setModelFieldsets($fieldsets): AbstractResource
    {
        if (empty($this->datas)) {
            return $this;
        }

        if (!$this->isModelObjectResource()) {
            throw new ApiException('Set models groups only for CModelObject resource.');
        }

        // filter
        $fieldsets_relations = $this->formatFieldsetByRelation(is_string($fieldsets) ? [$fieldsets] : $fieldsets);

        // check current fieldsets
        $this->checkFieldsets(
            $fieldsets_relations[self::CURRENT_RELATION_NAME] ?? [],
            ($this->model_class)::getConstants('FIELDSET')
        );

        $this->model_fieldsets = $fieldsets_relations;

        return $this;
    }

    private function extractSpecialFieldsets(string $fieldset): array
    {
        $available_fieldsets = ($this->model_class)::getConstants('FIELDSET');

        switch ($fieldset) {
            case RequestFieldsets::QUERY_KEYWORD_NONE:
                $fieldsets = [];
                break;
            case RequestFieldsets::QUERY_KEYWORD_ALL:
                $fieldsets = $available_fieldsets;
                break;
            default:
                $fieldsets = [$fieldset];
                break;
        }

        return $fieldsets;
    }

    /**
     * Extract the fieldsets and dispatch them to their relations.
     * Handle the all and none fieldset for the current object.
     */
    private function formatFieldsetByRelation(array $fieldsets): array
    {
        $fieldsets_relation = [];
        // Pour chaque fieldset de current extraire le "ALL" et "EMPTY" uniquement pour un tableau d'une taille de 1
        foreach ($this->orderFieldsetsByRelation($fieldsets) as $relation_name => $fieldsets) {
            if ($relation_name === self::CURRENT_RELATION_NAME) {
                if (count($fieldsets) === 1) {
                    $fieldsets_relation[$relation_name] = $this->extractSpecialFieldsets($fieldsets[0]);
                } else {
                    $fieldsets_relation[$relation_name] = $fieldsets;
                }
            } else {
                $fieldsets_relation[$relation_name] = array_unique($fieldsets);
            }
        }

        return $fieldsets_relation;
    }

    /**
     * Order the fieldsets by relations.
     * Fieldsets can be :
     * - current object : fieldset_name|all|none
     * - relation : relation_name.fielset_name|relation_name.all|relation_name.none
     */
    private function orderFieldsetsByRelation(array $fieldsets): array
    {
        $fieldsets_relations = [];
        foreach ($fieldsets as $fieldset) {
            // Get the relation name and the fieldsets
            [$relation_name, $explode_fieldset] = $this->separateFieldsetAndRelation($fieldset);

            // get fieldset
            $fieldset = count($explode_fieldset) > 1 ? implode(
                '.',
                array_slice($explode_fieldset, 1)
            ) : $explode_fieldset[0];

            $fieldsets_relations[$relation_name][] = $fieldset;
        }

        return $fieldsets_relations;
    }

    /**
     * @param array $fieldsets
     * @param array $available_fieldsets
     *
     * @return void
     * @throws ApiException
     */
    private function checkFieldsets(array $fieldsets, array $available_fieldsets): void
    {
        foreach ($fieldsets as $fieldset) {
            if ($fieldset === RequestFieldsets::QUERY_KEYWORD_NONE) {
                throw new ApiException("Unexpected reserved fieldsets 'none' in multiple declaration.");
            }

            if ($fieldset === RequestFieldsets::QUERY_KEYWORD_ALL) {
                throw new ApiException("Unexpected reserved fieldsets 'all' in multiple declaration.");
            }

            if (!in_array($fieldset, $available_fieldsets, true)) {
                throw new ApiException("Undefined fieldset '{$fieldset}' in class '{$this->model_class}'.");
            }
        }
    }

    /**
     * Separate the fieldset name from the relation.
     * If the fieldset is for the main object set 'current' as relation_name.
     */
    private function separateFieldsetAndRelation(string $fieldset): array
    {
        $explode_fieldset = explode('.', $fieldset);
        $relation_name    = count($explode_fieldset) > 1 ? $explode_fieldset[0] : self::CURRENT_RELATION_NAME;

        return [$relation_name, $explode_fieldset];
    }

    /**
     * Includes resource
     *
     * @param array|string $relations
     * @param array        $excluded_relations
     *
     * @return AbstractResource
     *
     * @throws ApiException
     */
    public function setModelRelations($relations, $excluded_relations = []): AbstractResource
    {
        if (empty($this->datas)) {
            return $this;
        }

        if (!$this->isModelObjectResource()) {
            throw new ApiException('Set models relations only for CModelObject resource.');
        }

        $available_relations = ($this->model_class)::getConstants('RELATION');

        if (is_string($relations)) {
            switch ($relations) {
                case RequestRelations::QUERY_KEYWORD_NONE:
                    $relations = [];
                    break;
                case RequestRelations::QUERY_KEYWORD_ALL:
                    $relations = $available_relations;
                    break;
                default:
                    $relations = [$relations];
                    break;
            }
        }

        foreach ($relations as $key => $relation) {
            if ($relation === RequestRelations::QUERY_KEYWORD_NONE) {
                throw new ApiException("Unexpected reserved relation 'none' in multiple declaration.");
            }

            if ($relation === RequestRelations::QUERY_KEYWORD_ALL) {
                throw new ApiException("Unexpected reserved relation 'all' in multiple declaration.");
            }

            if (!in_array($relation, $available_relations, true)) {
                throw new ApiException("Undefined relation '{$relation}' in class '{$this->model_class}'.");
            }

            if (in_array($relation, $excluded_relations)) {
                unset($relations[$key]);
            }
        }

        $this->model_relations = $relations;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModelObjectResource(): bool
    {
        return $this->model_class && is_subclass_of($this->model_class, CModelObject::class);
    }


    public function setType(string $type): AbstractResource
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @return mixed|null
     */
    public function getData($key)
    {
        if (array_key_exists($key, $this->datas)) {
            return $this->datas[$key];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getModelFieldsets(): ?array
    {
        return $this->model_fieldsets;
    }

    /**
     * @param string $relation_name
     *
     * @return mixed|null
     */
    public function getFieldsetsByRelation(string $relation_name = self::CURRENT_RELATION_NAME)
    {
        return $this->model_fieldsets[$relation_name] ?? null;
    }

    /**
     * @param array|string $fieldsets
     *
     * @return array
     * @throws ApiException
     */
    public function addModelFieldset($fieldsets)
    {
        if (is_string($fieldsets)) {
            $fieldsets = [$fieldsets];
        }

        $available_fieldsets = ($this->model_class)::getConstants('FIELDSET');

        foreach ($fieldsets as $fieldset) {
            // get resource name
            [$relation_name, $explode_fieldset] = $this->separateFieldsetAndRelation($fieldset);

            // Check current fieldsets
            if ($relation_name === self::CURRENT_RELATION_NAME) {
                $this->checkFieldsets($explode_fieldset, $available_fieldsets);
            }

            // init array
            if (!$this->model_fieldsets || !isset($this->model_fieldsets[$relation_name])) {
                $this->model_fieldsets[$relation_name] = [];
            }

            $fieldset = count($explode_fieldset) > 1 ? array_slice($explode_fieldset, 1) : $explode_fieldset;

            // add fieldset
            $this->model_fieldsets[$relation_name] = array_unique(
                array_merge($this->model_fieldsets[$relation_name], $fieldset)
            );
        }

        return $this->model_fieldsets;
    }

    /**
     * @param string|array $fieldsets
     *
     * @return bool
     */
    public function removeModelFieldset($fieldsets): bool
    {
        if (is_string($fieldsets)) {
            $fieldsets = [$fieldsets];
        }

        $count = 0;
        foreach ($fieldsets as $fieldset) {
            // get resource name
            [$relation_name, $explode_fieldset] = $this->separateFieldsetAndRelation($fieldset);

            // check has fieldset for resource name
            if (!$this->getFieldsetsByRelation($relation_name)) {
                continue;
            }

            $fieldset = implode(
                ".",
                count($explode_fieldset) > 1 ? array_slice($explode_fieldset, 1) : $explode_fieldset
            );

            // remove fieldset for resource name
            $count                                 += CMbArray::removeValue(
                $fieldset,
                $this->model_fieldsets[$relation_name]
            );
            $this->model_fieldsets[$relation_name] = array_values($this->model_fieldsets[$relation_name]);
        }

        return $count == count($fieldsets);
    }

    /**
     * @param string $relation_name
     *
     * @return bool
     */
    public function hasModelrelation(string $relation_name): bool
    {
        return in_array($relation_name, $this->model_relations ?? []);
    }

    /**
     * @param string $fieldset
     * @param string $relation_name
     *
     * @return bool
     */
    public function hasModelFieldset(string $fieldset): bool
    {
        [$relation_name, $fieldset] = $this->separateFieldsetAndRelation($fieldset);

        $fieldset = $relation_name !== self::CURRENT_RELATION_NAME ? implode(
            '.',
            array_slice($fieldset, 1)
        ) : $fieldset[0];

        return in_array($fieldset, $this->getFieldsetsByRelation($relation_name) ?? [], true);
    }

    /**
     * @return array
     */
    public function getModelRelations(): ?array
    {
        return $this->model_relations;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return array
     */
    public function getMetas(): array
    {
        return $this->metas;
    }

    /**
     * @return void
     */
    protected function setDefaultMetas(): void
    {
        // Add code to this function to add efault metadata on resources.
    }

    /**
     * @param string       $key
     * @param string|array $value
     *
     * @return AbstractResource
     */
    public function addMeta($key, $value): AbstractResource
    {
        $this->metas[$key] = $value;

        return $this;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function addMetas(array $array): AbstractResource
    {
        foreach ($array as $key => $value) {
            $this->addMeta($key, $value);
        }

        return $this;
    }

    /**
     * @param array $links
     *
     * @return AbstractResource
     */
    public function addLinks(array $links): AbstractResource
    {
        foreach ($links as $member => $link) {
            $this->links[$member] = $link;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getRecursionDepth(): int
    {
        return $this->recursion_depth;
    }

    /**
     * @param int $recursion_depth
     *
     * @return AbstractResource
     */
    public function setRecursionDepth(int $recursion_depth): self
    {
        $this->recursion_depth = $recursion_depth;

        return $this;
    }

    /**
     * Serialize resource to json:api formats
     * Merge top level & resource objects
     *
     * @return array
     */
    public function serialize(): array
    {
        return $this->createSerializer()->serialize();
    }

    /**
     * @return string
     */
    public function getSerializer(): string
    {
        return $this->serializer;
    }

    /**
     * @param string $serializer
     *
     * @return void
     * @throws ApiException
     */
    public function setSerializer(string $serializer): void
    {
        if (!is_subclass_of($serializer, AbstractSerializer::class)) {
            throw new ApiException('Invalid serializer ' . $serializer);
        }
        $this->serializer = $serializer;
    }

    /**
     * @return AbstractSerializer
     */
    public function createSerializer(): AbstractSerializer
    {
        return new $this->serializer($this);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $datas = $this->serialize();

        $datas = CMbArray::utf8Encoding($datas, true);

        return $datas;
    }

    /**
     * @return array|mixed
     */
    public function xmlSerialize()
    {
        return $this->jsonSerialize();
    }

    /**
     * @return string
     */
    public function getRequestUrl(): ?string
    {
        return $this->request_url;
    }

    /**
     * @param string $relation
     * @param array  $fieldsets
     *
     * @return AbstractResource
     * @throws ApiException
     */
    public function addFieldsetsOnRelation(
        string $relation,
        array $fieldsets
    ): AbstractResource {
        if (!$this->hasModelrelation($relation)) {
            return $this;
        }
        $this->addModelFieldset(
            array_map(
                function ($fieldset) use ($relation) {
                    return $relation . "." . $fieldset;
                },
                $fieldsets
            )
        );

        return $this;
    }

    /**
     * @param bool $model_schema
     *
     * @return $this
     */
    public function addMetasSchema(): AbstractResource
    {
        if ($this->isModelObjectResource()) {
            /** @var CModelObject $model */
            $model                             = new $this->model_class();
            $this->metas[self::SCHEMA_KEYWORD] = $model->getSchema($this->getModelFieldsets());
        }

        return $this;
    }

    public function addMetasPermissions(): AbstractResource
    {
        if (is_subclass_of($this->model_class, CStoredObject::class)) {
            /** @var CStoredObject $model */
            $model = new $this->model_class();

            $can = $model->canDo();

            if ($can->edit) {
                $this->metas[self::PERMISSIONS_KEYWORD] = [
                    'perm' => 'edit',
                ];
            } elseif ($can->read) {
                $this->metas[self::PERMISSIONS_KEYWORD] = [
                    'perm' => 'read',
                ];
            }
        }

        return $this;
    }

    public function getDatasTransformed(): array
    {
        return $this->datas_transformed;
    }

    public function getWithPermissions(): bool
    {
        return $this->with_permissions;
    }

    public function setWithPermissions(bool $with_permissions): void
    {
        $this->with_permissions = $with_permissions;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): ?RouterInterface
    {
        return $this->router;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function needRouter(): bool
    {
        return $this->isModelObjectResource() && $this->getRouter() === null;
    }

    public function getRequestRelations(): string
    {
        return $this->request_relations;
    }

    public function getRequestExcludedRelations(): string
    {
        return $this->request_excluded_relations;
    }

    public function getRequestFieldsets(): string
    {
        return $this->request_fieldsets;
    }
}
