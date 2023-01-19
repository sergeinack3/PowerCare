<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CIdentityProofType;

class IdentityProofTypeController extends CLegacyController
{
    public function indexIdentityProofTypes(): void
    {
        $this->checkPermAdmin();
        CView::checkin();

        $filter = new CIdentityProofType();
        $filter->active = '1';

        $this->renderSmarty('identity_proof_type/index', [
            'filter' => $filter,
            'types'  => CIdentityProofType::getActiveTypes(),
            'total'  => CIdentityProofType::count(),
            'page'   => 0
        ]);
    }

    public function filterIdentityProofTypes(): void
    {
        $this->checkPermAdmin();

        $code = CView::post('code', 'str');
        $label = CView::post('label', 'str');
        $trust_level = CView::post('trust_level', 'enum list|1|2|3');
        $active = CView::post('active', 'bool');
        $page = (int)CView::post('page', 'num default|0');

        CView::checkin();

        $this->renderSmarty('identity_proof_type/list', [
            'types' => CIdentityProofType::filter($code, $label, $trust_level, $active, $page),
            'total' => CIdentityProofType::count($code, $label, $trust_level),
            'page'  => $page
        ]);
    }

    /**
     * @throws CMbException
     */
    public function editIdentityProofType(): void
    {
        $this->checkPermAdmin();

        $identity_proof_type_id = CView::get('identity_proof_type_id', 'ref class|CIdentityProofType');

        CView::checkin();

        $type = CIdentityProofType::findOrNew($identity_proof_type_id);
        $this->renderSmarty('identity_proof_type/edit', ['type' => $type]);
    }
}
