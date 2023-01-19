<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\Ccam\CDevisCodageToPdfFile;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\PlanningOp\COperation;

class DevisLegacyController extends CLegacyController
{
    /**
     * @throws \Exception
     */
    public function codageDevis(): void
    {
        $this->checkPermRead();

        $object_id    = CView::get('object_id', 'ref meta|object_class');
        $object_class = CView::get('object_class', 'str');

        CView::checkin();

        /** @var CCodable $object */
        $object = CMbObject::loadFromGuid("$object_class-$object_id");

        CAccessMedicalData::logAccess($object);

        $object->loadRefPraticien();

        $devis                = new CDevisCodage();
        $devis->codable_class = $object->_class;
        $devis->codable_id    = $object->_id;
        $devis->loadMatchingObject();

        if (!$devis->_id) {
            $devis->event_type   = $object->_class;
            $devis->patient_id   = $object->loadRefPatient()->_id;
            $devis->praticien_id = $object->loadRefPraticien()->_id;
            if ($object->_class == 'CConsultation') {
                $devis->libelle = $object->motif;
                $object->loadRefPlageConsult();
                $devis->date = $object->_date;
            } elseif ($object->_class == 'COperation') {
                $devis->libelle = $object->libelle;
                $devis->date    = $object->date;
            }
            $devis->codes_ccam = $object->codes_ccam;
        }

        $this->renderSmarty('inc_devis_codage.tpl', [
            'devis' => $devis,
        ]);
    }

    public function editDevis(): void
    {
        $this->checkPermEdit();

        $devis_id = CView::get('devis_id', 'ref class|CDevisCodage');
        $action   = CView::get('action', 'str default|open');

        CView::checkin();

        $devis = new CDevisCodage();

        if ($devis_id) {
            $devis->load($devis_id);
            $devis->loadRefCodable();
        }

        if ($devis->_id) {
            $devis->canDo();
            $devis->loadRefPatient();
            $devis->loadRefPraticien();
            $devis->getActeExecution();
            $devis->countActes();
            $devis->loadRefsActes();

            foreach ($devis->_ref_actes as $_acte) {
                $_acte->loadRefExecutant();
            }

            $devis->loadExtCodesCCAM();
            $devis->loadPossibleActes();

            // Chargement des règles de codage
            $devis->loadRefsCodagesCCAM();
            foreach ($devis->_ref_codages_ccam as $_codages_by_prat) {
                foreach ($_codages_by_prat as $_codage) {
                    $_codage->loadPraticien()->loadRefFunction();
                    $_codage->loadActesCCAM();
                    $_codage->getTarifTotal();
                    foreach ($_codage->_ref_actes_ccam as $_acte) {
                        $_acte->getTarif();
                    }
                }
            }
        }

        // Chargement des praticiens
        $listAnesths = new CMediusers();
        $listAnesths = $listAnesths->loadAnesthesistes(PERM_EDIT);

        $listChirs = CConsultation::loadPraticiens(PERM_EDIT);

        //Initialisation d'un acte NGAP
        $acte_ngap = CActeNGAP::createEmptyFor($devis);
        // Liste des dents CCAM
        $dents       = CDentCCAM::loadList();
        $liste_dents = reset($dents);

        /* LPP */
        $acte_lpp = null;
        if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
            $devis->loadRefsActesLPP();

            foreach ($devis->_ref_actes_lpp as $_acte) {
                $_acte->loadRefExecutant();
                $_acte->_ref_executant->loadRefFunction();
            }

            $acte_lpp = CActeLPP::createFor($devis);
        }

        $user = CMediusers::get();
        $user->isPraticien();
        $user->isProfessionnelDeSante();

        $tarifs = CTarif::loadTarifsUser($devis->_ref_praticien);

        $file_category        = new CFilesCategory();
        $file_category->class = "CDevisCodage";
        $file_category->loadMatchingObjectEsc();

        if (!$file_category->_id) {
            CAppUI::setMsg(
                CAppUI::tr('CDevisCodage-configure a file category to generate pdf'),
                UI_MSG_ALERT
            );
            echo CAppUI::getMsg();
        }

        $template = 'open' === $action ? "inc_edit_devis_container.tpl" : "inc_edit_devis.tpl";

        $this->renderSmarty($template, [
            'devis'       => $devis,
            'acte_ngap'   => $acte_ngap,
            'acte_lpp'    => $acte_lpp,
            'liste_dents' => $liste_dents,
            'listAnesths' => $listAnesths,
            'listChirs'   => $listChirs,
            'user'        => $user,
            'tarifs'      => $tarifs,
        ]);
    }

    public function listDevis(): void
    {
        $this->checkPermRead();

        $object_class = CView::get('object_class', "str");
        $object_id    = CView::get('object_id', "ref meta|object_class");
        CView::checkin();

        $object = CMbObject::loadFromGuid("$object_class-$object_id");

        if ($object instanceof COperation || $object instanceof CConsultation || $object instanceof CEvenementPatient) {
            $object->loadRefPraticien();
            $object->loadRefPatient();
        }

        $list_devis = $object->loadBackRefs('devis_codage', 'creation_date ASC', null, 'devis_codage_id');

        foreach ($list_devis as $_devis) {
            $_devis->updateFormFields();
            $_devis->countActes();
        }

        $this->renderSmarty('inc_list_devis.tpl', [
            'object'     => $object,
            'list_devis' => $list_devis,
        ]);
    }

    public function printDevis(): void
    {
        $this->checkPermRead();

        $devis_id = CView::get('devis_id', 'ref class|CDevisCodage');

        CView::checkin();

        $devis = new CDevisCodage();
        $devis->load($devis_id);

        if ($devis->_id) {
            $devis->updateFormFields();
            $devis->loadRefPatient();
            $devis->loadRefCodable();
            $devis->loadRefPraticien();
            $devis->_ref_praticien->loadRefFunction();
            $devis->getActeExecution();
            $devis->countActes();
            $devis->loadRefsActes();
            $devis->loadRefsFraisDivers();

            foreach ($devis->_ref_actes_ccam as $_acte) {
                $_acte->getTarif();
            }
            foreach ($devis->_ref_frais_divers as $_frais) {
                $_frais->loadRefType();
            }


            $model = CCompteRendu::getSpecialModel($devis->_ref_praticien, $devis->_class, '[DEVIS]');

            if ($model->_id) {
                CCompteRendu::streamDocForObject($model, $devis);
            }
            // Stockage du devis
            try {
                CDevisCodageToPdfFile::generateFileFromDevisCodage($devis, true);
            } catch (Exception $e) {
            }

            $this->renderSmarty('print_devis_codage.tpl', ['devis' => $devis]);
        }
    }
}
