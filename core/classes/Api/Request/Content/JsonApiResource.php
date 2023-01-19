<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request\Content;

use Countable;
use Iterator;
use Ox\Core\CItemsIteratorTrait;

class JsonApiResource implements Countable, Iterator
{
    use CItemsIteratorTrait;

    public const TOP_LEVEL_META  = 'meta';
    public const TOP_LEVEL_DATA  = 'data';
    public const TOP_LEVEL_ERROR = 'error';

    public const TOP_LEVELS = [
        self::TOP_LEVEL_META,
        self::TOP_LEVEL_DATA,
        self::TOP_LEVEL_ERROR,
    ];

    /** @var int */
    private const DATA_LIMIT = 100;

    /** @var array */
    private $content;

    private $meta = [];

    private $items = [];

    /**
     * @throws RequestContentException
     */
    public function __construct(string $json_api)
    {
        $this->content = json_decode($json_api, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw RequestContentException::unableToDecodeContent();
        }

        $this->checkTopLevel();

        $this->createMeta();

        $this->createItems();
    }

    public function getItem(): JsonApiItem
    {
        return $this->current();
    }

    /**
     * @throws RequestContentException
     */
    private function checkTopLevel(): void
    {
        if (!array_key_exists(self::TOP_LEVEL_DATA, $this->content)) {
            throw RequestContentException::dataNodeIsMandatory();
        }
    }

    private function createMeta(): void
    {
        if (array_key_exists(self::TOP_LEVEL_META, $this->content)) {
            $this->meta = $this->content[self::TOP_LEVEL_META];
        }
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return JsonApiItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function isCollection(): bool
    {
        return !array_key_exists('type', $this->content['data']);
    }

    /**
     * @throws RequestContentException
     */
    private function createItems(): void
    {
        if (count($this->content['data']) > $this->getLimit()) {
            throw RequestContentException::tooManyObjects($this->getLimit());
        }

        if ($this->isCollection()) {
            foreach ($this->content['data'] as $data) {
                $this->items[] = new JsonApiItem($data);
            }
        } else {
            $this->items[] = new JsonApiItem($this->content['data']);
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    protected function getLimit(): int
    {
        return self::DATA_LIMIT;
    }
}
