<?php

/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Import\Framework\Entity\CImportEntity;

/**
 * Description
 */
class CImportLegacyController extends CLegacyController
{
    public function do_reset_entities(): void
    {
        $this->checkPermAdmin();

        $campaign_id = CView::postRefCheckEdit('campaign_id', 'ref class|CImportCampaign notNull');
        $class_name  = CView::post('type', 'str notNull');

        CView::checkin();

        $entity = new CImportEntity();
        $ds = $entity->getDS();

        $where = [
            'import_campaign_id' => $ds->prepare('= ?', $campaign_id),
            'external_class'     => $ds->prepare('= ?', $class_name),
        ];

        $del_ids = $entity->loadIds($where);

        if ($msg = $entity->deleteAll($del_ids)) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
        }

        CAppUI::stepAjax('CImportEntity-Msg-delete|pl', UI_MSG_OK);
        CApp::rip();
    }
}
