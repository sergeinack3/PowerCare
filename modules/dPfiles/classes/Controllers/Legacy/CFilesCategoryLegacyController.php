<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFilesCategoryDispatcher;

/**
 * Description
 */
class CFilesCategoryLegacyController extends CLegacyController
{
    public function vw_dispatch_docs(): void
    {
        $this->checkPermAdmin();

        $cat_id = CView::getRefCheckEdit('cat_id', 'ref class|CFilesCategory notNull');

        CView::checkin();

        CView::enforceSlave();

        $cat = CFilesCategory::findOrFail($cat_id);
        if ($cat->group_id !== null) {
            CAppUI::stepAjax('CFilesCategoryDispatcher-Error-Only files without group can be dispatch', UI_MSG_ERROR);
        }

        $dispatcher = new CFilesCategoryDispatcher($cat);
        $stats      = $dispatcher->getStats();

        $this->renderSmarty(
            'files_category_dispatch/vw_dispatch_docs',
            [
                'stats' => $stats,
                'cat'   => $cat,
            ]
        );
    }

    public function do_dispatch_files_cat(): void
    {
        $this->checkPermAdmin();

        $cat_id   = CView::postRefCheckEdit('cat_id', 'ref class|CFilesCategory notNull');
        $group_id = CView::postRefCheckEdit('group_id', 'ref class|CGroups notNull');

        CView::checkin();

        CApp::setMemoryLimit('2048M');
        CApp::setTimeLimit(300);

        $cat = CFilesCategory::findOrFail($cat_id);
        if ($cat->group_id !== null) {
            CAppUI::stepAjax('CFilesCategoryDispatcher-Error-Only files without group can be dispatch', UI_MSG_ERROR);
        }

        $group = CGroups::findOrFail($group_id);

        $dispatcher = new CFilesCategoryDispatcher($cat);
        foreach ($dispatcher->dispatch($group) as $msg) {
            CAppUI::setMsg(...$msg);
        }

        echo CAppUI::getMsg();

        CApp::rip();
    }
}
