<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Exception;

class ActeNGAPLegacyController extends CLegacyController
{
    /**
     * Edit DEP of NGAP Act
     *
     * @return void
     * @throws Exception
     */
    public function editDEP(): void
    {
        $this->checkPermRead();

        $act_id   = CView::get('act_id', 'num');
        $view     = CView::get('view', 'str');
        $readonly = CView::get('readonly', 'num');

        // Field
        $dep                    = CView::get('dep', 'str');
        $date_request_agreement = CView::get('date_request_agreement', 'str');
        $response_agreement     = CView::get('response_agreement', 'str');

        CView::checkin();

        $act_ngap = CActeNGAP::findOrNew($act_id);

        $this->renderSmarty('actes/vw_edit_dep_ngap', [
            'act_ngap'               => $act_ngap,
            'view'                   => $view,
            'readonly'               => $readonly,
            // Field
            'dep'                    => $dep,
            'date_request_agreement' => $date_request_agreement,
            'response_agreement'     => $response_agreement,
        ]);
    }

    /**
     * View for create or edit an NGAP act
     * @return void
     * @throws \Exception
     */
    public function editComment(): void
    {
        $this->checkPermRead();

        $acte_id        = CView::get('acte_id', 'num');
        $name_form      = CView::get('name_form', 'str');
        $comment_acte   = CView::get('comment_acte', 'str');

        CView::checkin();

        $acte_NGAP = CActeNGAP::findOrNew($acte_id);

        $this->renderSmarty('vw_edit_comment_acte_ngap.tpl', [
            'acte_ngap'     => $acte_NGAP,
            'name_form'     => $name_form,
            'comment_acte'  => $comment_acte
        ]);
    }
}
