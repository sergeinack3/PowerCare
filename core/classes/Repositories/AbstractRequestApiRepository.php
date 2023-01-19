<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Repositories;

use Exception;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\CStoredObject;

/**
 * Only use this repository to get initialize it from a request API (using autowire for instance).
 * Abstract repository that can load a list of objects using an initialization from a RequestApi.
 * Repositories can have multiple functions to do specific loads. Do not make a function that take an array of SQL
 * conditions, instead make functions depending on your functionalities.
 */
abstract class AbstractRequestApiRepository
{
    private const SEARCH_KEYWORD = 'search';

    /** @var array */
    protected $where = [];

    /** @var string */
    protected $seek_keywords;

    /** @var array */
    protected $order = [];

    /** @var string */
    protected $limit;

    /** @var CStoredObject */
    protected $object;

    /**
     * Return an instance of the object to use.
     * This function can be mocked to use a mock of the object instead in the unit tests.
     */
    abstract protected function getObjectInstance(): CStoredObject;

    /**
     * Massload the relations asked to avoid loading them unitairly later.
     */
    abstract protected function massLoadRelation(array $objects, string $relation): void;

    /**
     * Initialize the object instance.
     */
    public function __construct()
    {
        $this->object = $this->getObjectInstance();
    }

    /**
     * Initialization method which is called before autowire the object.
     * This method will initialize the $where, $order and $limit using the RequestApi.
     * If the GET argument 'search' is set, use it to make a seek instead of basic loadList.
     *
     * @see   ../../services/Repositories/repositories.yml
     *
     * @throws ApiRequestException
     */
    public function initFromRequest(RequestApi $request_api): void
    {
        if ($request_filter = $request_api->getRequestFilter()) {
            $this->where = $request_filter->getSqlFilters($this->object->getDS());
        }

        if ($search = $request_api->getRequest()->get(self::SEARCH_KEYWORD)) {
            $this->seek_keywords = $search;
        }

        if ($order = $request_api->getSortAsSql()) {
            $this->order = $order;
        }

        $this->limit = $request_api->getLimitAsSql();
    }

    /**
     * Load a list of $object using the repository parameters.
     *
     * @throws Exception
     */
    public function find(): array
    {
        return $this->seek_keywords
        ? $this->object->seek($this->seek_keywords, $this->where, $this->limit, true, null, $this->order)
        : $this->object->loadList($this->where, $this->order, $this->limit);
    }

    /**
     * Count a list of $object using the repository parameters.
     *
     * @throws Exception
     */
    public function count(): int
    {
        if ($this->seek_keywords) {
            if ($this->object->_totalSeek === null) {
                // Making the seek query will count the data.
                $this->find();
            }

            $total = $this->object->_totalSeek;
        } else {
            $total = $this->object->countList($this->where);
        }

        return $total;
    }

    /**
     * Mass load objects for each relation. If the 'all' relation is set, massload objects for all relations only once.
     */
    public function massLoadRelations(array $objects, array $relations): void
    {
        if (in_array(RequestRelations::QUERY_KEYWORD_ALL, $relations)) {
            $this->massLoadRelation($objects, RequestRelations::QUERY_KEYWORD_ALL);
        } else {
            foreach ($relations as $relation) {
                $this->massLoadRelation($objects, $relation);
            }
        }
    }

    public function setLimit(string $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOrder(array $order): self
    {
        $this->order = $order;

        return $this;
    }
}
