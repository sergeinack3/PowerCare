<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\CCdaTools;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Xds\Factory\CXDSFactory;
use Ox\Interop\Xds\XDM\CXDMRepository;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\CSejour;

class CDAControllerLegacy extends CLegacyController
{
    public function ajax_create_cda_vsm(): void
    {
        /** @var CSejour|CConsultation $object */
        $object = $this->loadObject();
        $file_category_id = CView::get('file_category_id', 'ref class|CFilesCategory');
        CView::checkin();

        // Création du dossier médical car indispensable pour la création du CDA
        $patient = $object->loadRefPatient();
        CDossierMedical::dossierMedicalId($patient->_id, $patient->_class);

        CAccessMedicalData::logAccess($object);

        // generate XDM
        $repo_xdm = new CXDMRepository($object, CCDAFactory::TYPE_VSM, CXDSFactory::TYPE_ANS);

        $options_cda = [];
        if ($file_category_id) {
            $options_cda['file_category_id'] = $file_category_id;
        }

        $this->render($repo_xdm, 'VSM', $options_cda);

        // Génération du PDF de la VSM
        $cda_file = $repo_xdm->getFileCDA();
        CCdaTools::generatePdfVSM($object, $cda_file);
    }

    public function ajax_create_vaccination(): void
    {
        /** @var CSejour|CConsultation $object */
        $object = $this->loadObject();
        $injection_id = CView::get("injection_id", "str");
        CView::checkin();

        if (!$injection_id) {
            CAppUI::stepAjax("injection_id ne peut pas être nul", UI_MSG_ERROR);
        }

        $injection = new CInjection();
        $injection->load($injection_id);
        if (!$injection->_id) {
            CAppUI::stepAjax("Impossible de charger l'injection", UI_MSG_ERROR);
        }

        // on link l'injection avec la consultation
        $object->addToStore(CInjection::class, $injection);

        $repository = new CXDMRepository($object, CCDAFactory::TYPE_VACCINATION_NOTE, CXDSFactory::TYPE_ANS);

        $this->render($repository, 'Vaccination');
    }

    public function ajax_create_ldl(): void
    {
        /** @var CSejour|CConsultation $object */
        $object   = $this->loadObject();
        $type_ldl = CView::get("type_ldl", "str");
        CView::checkin();

        if (($type_ldl == CCDAFactory::TYPE_LDL_SES && !$object instanceof CSejour) || ($type_ldl == CCDAFactory::TYPE_LDL_EES && (!$object instanceof CConsultation && !$object instanceof CSejour))) {
            CAppUI::stepAjax("ObjectClass incorrect", UI_MSG_ERROR);
        }

        $patient = $object->loadRefPatient();

        if (!$patient || !$patient->_id) {
            CAppUI::stepAjax('common-error-Object not found', UI_MSG_ERROR);
        }

        // Création du dossier médical car indispensable pour la création du CDA
        $patient = $object->loadRefPatient();
        CDossierMedical::dossierMedicalId($patient->_id, $patient->_class);

        // generate XDM
        $repo_xdm = new CXDMRepository($object, $type_ldl, CXDSFactory::TYPE_ANS);

        $this->render($repo_xdm, 'Lettre de liaison');
    }

    public function ajax_create_IHE_XDM_CDA_LVL1(): void
    {
        /** @var CFile|CCompteRendu $object */
        $object = $this->loadDocumentItem();

        // generate XDM
        $repo_xdm = new CXDMRepository($object, CCDAFactory::TYPE_ANS_L1, CXDSFactory::TYPE_ANS);

        $this->render($repo_xdm, 'CDA Level 1');
    }

    public function ajax_create_IHE_XDM_CDA_LVL3(): void
    {
        $file_id = CView::get('file_id', 'ref class|CFile');
        CView::checkin();

        /** @var CFile|CCompteRendu $object */
        $file = CMbObject::loadFromGuid("CFile-$file_id");

        // generate XDM
        $repo_xdm = new CXDMRepository($file->loadTargetObject(), CCDAFactory::TYPE_VACCINATION_NOTE, CXDSFactory::TYPE_ANS);
        $repo_xdm->setFileCDA($file);
        $this->render($repo_xdm, 'CDA Level 3');
    }

    private function loadObject(): CStoredObject
    {
        $object_id    = CView::get("object_id", "str");
        $object_class = CView::get("object_class", "str");

        if (!$object_id) {
            CAppUI::stepAjax("ObjectId ne peut pas être nul", UI_MSG_ERROR);
        }

        if (!$object_class) {
            CAppUI::stepAjax("object_class ne peut pas être nul", UI_MSG_ERROR);
        }

        if ($object_class !== "CSejour" && $object_class !== "CConsultation") {
            CAppUI::stepAjax("ObjectClass doit être égale à CSejour ou CConsultation", UI_MSG_ERROR);
        }

        $object = new $object_class();
        $object->load($object_id);

        if (!$object || !$object->_id) {
            CAppUI::stepAjax('common-error-Object not found', UI_MSG_ERROR);
        }

        return $object;
    }

    private function loadDocumentItem(): CStoredObject
    {
        $object_id    = CView::get("object_id", "str");
        $object_class = CView::get("object_class", "str");
        CView::checkin();

        if (!$object_id) {
            CAppUI::stepAjax("ObjectId ne peut pas être nul", UI_MSG_ERROR);
        }

        if (!$object_class) {
            CAppUI::stepAjax("object_class ne peut pas être nul", UI_MSG_ERROR);
        }

        if ($object_class !== "CFile" && $object_class !== "CCompteRendu") {
            CAppUI::stepAjax("ObjectClass doit être égale à CSejour ou CConsultation", UI_MSG_ERROR);
        }

        $object = new $object_class();
        $object->load($object_id);

        if (!$object || !$object->_id) {
            CAppUI::stepAjax('common-error-Object not found', UI_MSG_ERROR);
        }

        return $object;
    }

    private function render(CXDMRepository $repository, string $type, array $options_cda = []): void
    {
        // generate XDM
        try {
            $repository->generateXDM($options_cda);
        } catch (CMbException $e) {
            $repository->getReport()->addData($e->getMessage(), CItemReport::SEVERITY_ERROR);
        }

        // errors pending generation of xdm
        if ($repository->hasErrors()) {
            $smarty = new CSmartyDP();
            $smarty->assign('report', $repository->getReport());
            $smarty->display("inc_create_cda_vsm.tpl");

            return;
        }

        CAppUI::stepAjax("Fichier $type créé");
    }
}
