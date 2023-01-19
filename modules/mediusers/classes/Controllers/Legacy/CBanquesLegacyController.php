<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Controllers\Legacy;

use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CBanque;

/**
 * Description
 */
class CBanquesLegacyController extends CLegacyController
{
    public function listBanques(): void
    {
//        CCanDo::checkRead();
        $current_id = CView::get("current_id", "ref class|CBanque", true);
        CView::checkin();

        $this->renderSmarty("inc_list_banques", [
            'current_id' => $current_id,
            'banques'    => CBanque::loadAllBanques(),
        ]);
    }
}
