<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Transformers;

class ArrayTransformer extends AbstractTransformer
{
    /**
     * @return array
     */
    public function createDatas(): array
    {
        $datas      = $this->item->getDatas();
        $this->type = $this->item->getType() ?? 'undefined';

        // Id
        if (array_key_exists('id', $datas)) {
            $this->id = $datas['id'];
            unset($datas['id']);
        } else {
            $this->id = $this->createIdFromData($datas);
        }

        // Attributes
        foreach ($datas as $key => $sub_datas) {
            // todo filter
            $this->attributes[$key] = $sub_datas;
        }

        $this->links = $this->item->getLinks();

        if ($meta = $this->item->getMetas()) {
            $this->meta = $meta;
        }

        return $this->render();
    }
}
