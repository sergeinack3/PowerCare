<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Resources;

use ArrayAccess;
use Countable;
use Iterator;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestLimit;
use Ox\Core\CItemsIteratorTrait;

class Collection extends AbstractResource implements ArrayAccess, Countable, Iterator
{
    use CItemsIteratorTrait;

    public const META_COUNT = 'count';
    public const META_TOTAL = 'total';

    /** @var Item[] */
    private $items = [];

    /**
     * Collection constructor.
     *
     * @param array $datas
     *
     * @throws ApiException
     */
    public function __construct(array $datas)
    {
        $model_class = null;
        if (!empty($datas)) {
            $first_element = $datas[array_key_first($datas)];
            $model_class   = is_object($first_element) ? get_class($first_element) : null;
        }

        parent::__construct($datas, $model_class);

        $this->createItems();
    }

    /**
     * @return void
     * @throws ApiException
     */
    private function createItems(): void
    {
        foreach ($this->datas as $key => $data) {
            // Do not add default meta for collection items.
            $this->items[] = new Item($data, false);
        }
    }

    /**
     * @inheritDoc
     * @throws ApiException
     */
    public function transform(): array
    {
        $datas_transformed = [];
        foreach ($this->items as $key => $item) {
            $item                = $this->propageSettings($item);
            $datas_transformed[] = $item->transform();
        }

        return $this->datas_transformed = $datas_transformed;
    }

    /**
     * @param AbstractResource $item
     *
     * @return AbstractResource $item
     * @throws ApiException
     */
    private function propageSettings(AbstractResource $item): AbstractResource
    {
        if ($this->type) {
            $item->setType($this->type);
        }

        if ($this->model_fieldsets) {
            $model_fieldsets = [];
            foreach ($this->model_fieldsets as $relation => $model_fieldset) {
                foreach ($model_fieldset as $fieldset) {
                    $model_fieldsets[] = $relation === self::CURRENT_RELATION_NAME ? "$fieldset" : "$relation.$fieldset";
                }
            }

            $item->setModelFieldsets($model_fieldsets);
        }

        if ($this->model_relations) {
            $item->setModelRelations($this->model_relations);
        }

        if ($this->with_permissions) {
            $item->setWithPermissions($this->with_permissions);
        }

        if ($this->router) {
            $item->setRouter($this->router);
        }

        return $item;
    }

    /**
     * @param int      $offset
     * @param int      $limit
     * @param int|null $total
     *
     * @return Collection
     */
    public function createLinksPagination(int $offset, int $limit, int $total = null): Collection
    {
        $links = [];

        // self
        $links['self'] = $this->createLinkUrl($offset, $limit);

        // next
        $next          = $offset + $limit;
        $links['next'] = $this->createLinkUrl($next, $limit);

        // prev
        $prev = $offset - $limit;
        // No prev link for the first page.
        if ($prev >= 0) {
            $links['prev'] = $this->createLinkUrl($prev, $limit);
        }

        // first
        $links['first'] = $this->createLinkUrl(RequestLimit::OFFSET_DEFAULT, $limit);

        // last
        if ($total !== null) {
            // No next link for last page
            if (($offset + $limit) >= $total || $total === 0) {
                unset($links['next']);
            }

            $modulus = $total % $limit;

            // If the limit is a modulus of the total the last page must be a page with records
            if ($modulus === 0 && ($total - $limit) >= 0) {
                $modulus = $limit;
            }

            $links['last'] = $this->createLinkUrl($total - $modulus, $limit);

            $this->addMeta(self::META_TOTAL, $total);
        }

        $this->addLinks($links);

        return $this;
    }

    /**
     * @param int $offset
     *
     * @param int $limit
     *
     * @return string
     */
    private function createLinkUrl($offset, $limit): string
    {
        if ($query = parse_url($this->request_url ?? "", PHP_URL_QUERY)) {
            $params = explode('&', urldecode($query));

            foreach ($params as $key => $_param) {
                if (strpos($_param, 'offset=') === 0 || strpos($_param, 'limit=') === 0) {
                    unset($params[$key]);
                }
            }

            $params[] = 'offset=' . $offset;
            $params[] = 'limit=' . $limit;

            // Compat with symfony normalized query string
            sort($params);

            return str_replace($query, implode('&', $params), $this->request_url);
        }

        return $this->request_url .= '?offset=' . $offset . '&limit=' . $limit;
    }

    /**
     * @return void
     */
    protected function setDefaultMetas(): void
    {
        parent::setDefaultMetas();

        $this->addMeta(self::META_COUNT, count($this->datas));
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}
