<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CBanque;

class BankLegacyController extends CLegacyController
{
    /**
     * Show Banks
     *
     * @return void
     * @throws Exception
     */
    public function showBanks(): void
    {
        $this->checkPerm();

        $banks = CBanque::loadAllBanques();

        $this->renderSmarty(
            "vw_banques",
            [
                "banks" => $banks,
            ]
        );
    }

    /**
     * Edit Bank
     *
     * @return void
     * @throws Exception
     */
    public function editBank(): void
    {
        $this->checkPerm();

        $bank_id = CView::get("banque_id", "ref class|CBanque");

        CView::checkin();

        $bank = CBanque::findOrNew($bank_id);

        $this->renderSmarty(
            "inc_banques_edit",
            [
                "bank" => $bank,
            ]
        );
    }
}
