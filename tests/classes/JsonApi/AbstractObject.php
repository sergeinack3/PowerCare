<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\JsonApi;

use JsonSerializable;

abstract class AbstractObject implements JsonSerializable
{
    public const DATA     = 'data';
    public const LINKS    = 'links';
    public const META     = 'meta';
    public const INCLUDED = 'included';

    protected array $links    = [];
    protected array $meta     = [];
    protected array $included = [];

    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function hasLink(string $name): bool
    {
        return array_key_exists($name, $this->links);
    }

    /**
     * @return mixed|null
     */
    public function getLink(string $name)
    {
        return $this->links[$name] ?? null;
    }

    public function getMetas(): array
    {
        return $this->meta;
    }

    public function hasMeta(string $name): bool
    {
        return array_key_exists($name, $this->meta);
    }

    /**
     * @return mixed|null
     */
    public function getMeta(string $name)
    {
        return $this->meta[$name] ?? null;
    }

    public function setIncluded(array $included): self
    {
        $included_items = [];

        foreach ($included as $include) {
            $included_items[] = Item::createFromArray($include, true);
        }

        $this->included = $included_items;

        return $this;
    }

    public function hasIncluded(): bool
    {
        return !empty($this->included);
    }

    public function getIncluded(): array
    {
        return $this->included;
    }
}
