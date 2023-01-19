<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Serializers;

use Ox\Core\Api\Resources\Item;

class ArraySerializer extends AbstractSerializer
{
    /**
     * @inheritDoc
     */
    public function serialize(): array
    {
        $transform = $this->resource->transform();

        $serial = [
            'datas' => ($this->resource instanceof Item)
                ? $transform['datas'] : $transform,
            'metas' => $this->resource->getMetas(),
            'links' => $this->resource->getLinks(),
        ];

        if (isset($transform['relationships'])) {
            $serial['relationships'] = $transform['relationships'];
        }

        return $serial;
    }
}
