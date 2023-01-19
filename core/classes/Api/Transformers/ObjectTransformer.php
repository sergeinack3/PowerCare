<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Transformers;

class ObjectTransformer extends AbstractTransformer
{
    /**
     * @return array
     */
    public function createDatas(): array
    {
        $object = $this->item->getDatas();

        // id
        if (property_exists($object, 'id')) {
            $this->id = $object->id;
        } else {
            $this->id = $this->createIdFromData($object);
        }

        // type
        $this->type = $this->item->getType() ?? 'undefined';


        // Attributes
        foreach (get_object_vars($object) as $key => $sub_datas) {
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
