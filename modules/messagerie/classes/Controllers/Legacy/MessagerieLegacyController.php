<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;

class MessagerieLegacyController extends CLegacyController
{
    public function ajax_reload_external(): void
    {
        $this->checkPerm();

        CView::checkin();

        $messagerie = CAppUI::getMessagerieInfo();

        $this->renderSmarty('inc_external_messagerie_menu', ['messagerie' => $messagerie]);
    }
}
