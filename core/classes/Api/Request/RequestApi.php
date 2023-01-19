<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Request\Content\JsonApiResource;
use Ox\Core\Api\Request\Content\RequestContent;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\CModelObject;
use Ox\Core\CModelObjectCollection;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Mediboard\Etablissement\CGroups;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestApi
{
    use RequestHelperTrait;

    /** @var RequestFormats */
    protected $request_formats;

    /** @var RequestLimit */
    protected $request_limit;

    /** @var RequestSort */
    protected $request_sort;

    /** @var RequestRelations */
    protected $request_relations;

    /** @var RequestFieldsets */
    protected $request_fieldsets;

    /** @var RequestFilter */
    protected $request_filter;

    /** @var RequestLanguages */
    protected $request_languages;

    /** @var RequestGroup */
    private $request_group;

    /** @var RequestContent */
    private $request_content;

    /** @var string */
    private $uri;

    private const METHOD_POST = 'POST';
    private const METHOD_PUT  = 'PUT';

    /**
     * RequestApi constructor. Must use RequestStack for DI purpose
     *
     * @param RequestStack $stack
     *
     * @throws ApiRequestException|ApiException
     */
    public function __construct(RequestStack $stack)
    {
        $request = $stack->getCurrentRequest();

        $this->init($request);
    }

    /**
     * Init self using the Request.
     *
     * @throws ApiException|ApiRequestException
     */
    private function init(Request $request): void
    {
        $this->request           = $request;
        $this->request_formats   = new RequestFormats($request);
        $this->request_limit     = new RequestLimit($request);
        $this->request_sort      = new RequestSort($request);
        $this->request_relations = new RequestRelations($request);
        $this->request_fieldsets = new RequestFieldsets($request);
        $this->request_filter    = new RequestFilter($request);
        $this->request_languages = new RequestLanguages($request);
        $this->request_content   = new RequestContent($request);

        if (!$this->isRequestPublic($request)) {
            $this->request_group = new RequestGroup($request);
        }
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Allow the creation of RequestApi from a Request object.
     *
     * @throws ApiException|ApiRequestException
     */
    public static function createFromRequest(Request $request): self
    {
        $stack = new RequestStack();
        $stack->push($request);

        return new self($stack);
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->request_relations->getRelations();
    }

    /**
     * @param string $relation
     *
     * @return bool
     */
    public function hasRelation(string $relation): bool
    {
        return in_array($relation, $this->getRelations(), true);
    }

    /**
     * @return array
     */
    public function getRelationsExcluded(): array
    {
        return $this->request_relations->getRelationsExcludes();
    }

    /**
     * @return array
     */
    public function getFieldsets(): array
    {
        return $this->request_fieldsets->getFieldsets();
    }

    /**
     * @param string $fieldset
     *
     * @return bool
     */
    public function hasFieldset(string $fieldset): bool
    {
        return in_array($fieldset, $this->getFieldsets(), true);
    }

    /**
     * @return array
     */
    public function getFormats(): array
    {
        return $this->request_formats->getFormats();
    }

    /**
     * @return string
     */
    public function getFormatsExpected(): string
    {
        return $this->request_formats->getExpected();
    }

    /**
     * @return array|null
     */
    public function getSort(): ?array
    {
        return $this->request_sort->getFields();
    }

    /**
     * @param string $default
     *
     * @return null|string
     * @example [lorem asc, ipsum desc]
     */
    public function getSortAsSql(string $default = null): ?string
    {
        return $this->request_sort->getSqlOrderBy($default);
    }

    public function getRequestLimit(): RequestLimit
    {
        return $this->request_limit;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->request_limit->getLimit();
    }

    /**
     * @return mixed|null
     */
    public function getOffset()
    {
        return $this->request_limit->getOffset();
    }

    /**
     * @return string
     * @example [offset, limit]
     */
    public function getLimitAsSql(): string
    {
        return $this->request_limit->getSqlLimit();
    }

    /**
     * @param CSQLDataSource $ds
     * @param callable[]     $sanitize
     *
     * @return array
     * @throws ApiRequestException
     */
    public function getFilterAsSQL(CSQLDataSource $ds, array $sanitize = []): array
    {
        return $this->request_filter->getSqlFilters($ds, $sanitize);
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->request_filter->getFilters();
    }

    public function isFiltersEmpty(): bool
    {
        return $this->request_filter->isEmpty();
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->request_languages->getLanguage();
    }

    /**
     * @return string|null
     */
    public function getLanguageExpected(): ?string
    {
        return $this->request_languages->getExpected();
    }

    /**
     * @return RequestFilter
     */
    public function getRequestFilter(): RequestFilter
    {
        return $this->request_filter;
    }

    public function getRequestEtags(): array
    {
        return $this->request->getETags();
    }

    public function getGroup(): CGroups
    {
        return $this->request_group->getGroup();
    }

    /**
     * @throws RequestContentException
     */
    public function getResource(): JsonApiResource
    {
        return $this->request_content->getJsonApiResource();
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function isMethodPost(): string
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    public function isMethodPut(): string
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Get a CModelObject of class $object_class from the request body.
     *
     * @param string|CStoredObject $object Class name or object in case of update.
     *
     * @throws RequestContentException|ApiException
     */
    public function getModelObject($object, array $fieldsets = [], array $fields = []): CModelObject
    {
        return $this->convertItemToModelObject($this->getSingleItem(), $object, $fieldsets, $fields);
    }

    /**
     * Get a single item from the body content. If there is more than 1 object in the content, throw an exception.
     *
     * @throws ApiException
     */
    private function getSingleItem(): ?JsonApiItem
    {
        $items = $this->getResource()->getItems();
        if (count($items) > 1) {
            throw new ApiException('Too many objects to convert');
        }

        return array_shift($items);
    }

    /**
     * @throws RequestContentException
     */
    public function getModelObjectCollection(
        string $object_class,
        array $fieldsets = [],
        array $fields = []
    ): CModelObjectCollection {
        $collection = new CModelObjectCollection();

        foreach ($this->getResource()->getItems() as $item) {
            $collection->add($this->convertItemToModelObject($item, $object_class, $fieldsets, $fields));
        }

        return $collection;
    }

    /**
     * Convert a JsonApiItem to a CModelObject of class $object_class.
     *
     * @param string|CStoredObject $object Class name or object for update.
     *
     * @throws RequestContentException|ApiException
     */
    private function convertItemToModelObject(
        JsonApiItem $api_item,
        $object,
        array $fieldsets = [],
        array $fields = []
    ): CModelObject {
        if (empty($fieldsets) && empty($fields)) {
            $fieldsets = [CModelObject::FIELDSET_DEFAULT];
        }

        if (is_object($object)) {
            $api_item->setModelObject($object);
        } else {
            $api_item->createModelObject($object, ($this->isMethodPost() || $this->isMethodPut()));
        }

        return $api_item->hydrateObject($fieldsets, $fields)->getModelObject();
    }


    /**
     * @param bool   $json_decode
     * @param string $encode_to
     * @param string $encode_from
     *
     * @return false|mixed|resource|string|null
     * @throws Exception
     */
    public function getContent(bool $json_decode = true, string $encode_to = null, string $encode_from = 'UTF-8')
    {
        return $this->request_content->getContent($json_decode, $encode_to, $encode_from);
    }

    /**
     * @param string $parameter_name
     *
     * @return mixed
     * @throws ApiRequestException
     */
    public function getRequestParameter(string $parameter_name)
    {
        $parameters = get_object_vars($this);
        foreach ($parameters as $parameter) {
            if (!is_subclass_of($parameter, IRequestParameter::class)) {
                continue;
            }

            if (is_a($parameter, $parameter_name)) {
                return $parameter;
            }
        }

        throw new ApiRequestException(
            "Invalid parameter '{$parameter_name}', parameter must implement IRequestParameter"
        );
    }

    /**
     * Keep the same object but reset it using the new Request.
     *
     * @throws ApiException|ApiRequestException
     */
    public function resetFromRequest(Request $request): void
    {
        $this->init($request);
    }

    public function getUri(): string
    {
        return $this->uri ?? $this->request->getUri();
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
