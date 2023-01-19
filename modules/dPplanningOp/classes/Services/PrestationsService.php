<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Services;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Mediboard\Hospi\CItemLiaison;

class PrestationsService implements IShortNameAutoloadable
{
    /**
     * Clone binding properties
     *
     * @param CItemLiaison $item_dest   La destination
     * @param CItemLiaison $item_source La source
     *
     * @return void
     */
    public function copyLiaison(CItemLiaison $item_dest, CItemLiaison $item_source): void
    {
        $item_dest->item_souhait_id                    = $item_source->item_souhait_id;
        $item_dest->item_realise_id                    = $item_source->item_realise_id;
        $item_dest->sous_item_id                       = $item_source->sous_item_id;
        $item_dest->_ref_item->_id                     = $item_source->_ref_item->_id;
        $item_dest->_ref_item->nom                     = $item_source->_ref_item->nom;
        $item_dest->_ref_item->object_id               = $item_source->_ref_item->object_id;
        $item_dest->_ref_item->rank                    = $item_source->_ref_item->rank;
        $item_dest->_ref_item->color                   = $item_source->_ref_item->color;
        $item_dest->_ref_item->actif                   = $item_source->_ref_item->actif;
        $item_dest->_ref_item_realise->_id             = $item_source->_ref_item_realise->_id;
        $item_dest->_ref_item_realise->nom             = $item_source->_ref_item_realise->nom;
        $item_dest->_ref_item_realise->object_id       = $item_source->_ref_item_realise->object_id;
        $item_dest->_ref_item_realise->rank            = $item_source->_ref_item_realise->rank;
        $item_dest->_ref_item_realise->color           = $item_source->_ref_item_realise->color;
        $item_dest->_ref_item_realise->actif           = $item_source->_ref_item_realise->actif;
        $item_dest->_ref_sous_item->_id                = $item_source->_ref_sous_item->_id;
        $item_dest->_ref_sous_item->nom                = $item_source->_ref_sous_item->nom;
        $item_dest->_ref_sous_item->actif              = $item_source->_ref_sous_item->actif;
        $item_dest->_ref_sous_item->item_prestation_id = $item_source->_ref_sous_item->item_prestation_id;
    }
}
