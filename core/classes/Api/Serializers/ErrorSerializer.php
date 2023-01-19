<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Serializers;

use Ox\Core\Api\Resources\Item;

class ErrorSerializer extends AbstractSerializer
{
    public const KEY_ERRORS = 'errors';

    /**
     * @inheritDoc
     */
    public function serialize(): array
    {
        $datas = $this->resource->transform();

        if ($this->resource instanceof Item) {
            unset($datas['datas']['_type'], $datas['datas']['_id']);

            return [
                self::KEY_ERRORS => $datas['datas'],
            ];
        } else {
            $return = [];
            foreach ($datas as $_data) {
                unset($_data['datas']['_type'], $_data['datas']['_id']);

                $return[] = [self::KEY_ERRORS => $_data['datas']];
            }

            return $return;
        }
    }
}
