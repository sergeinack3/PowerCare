<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Core\Sessions\CSessionManager;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Ccam\CModelCodage;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;

class CodageLegacyController extends CLegacyController
{
    public function checkLockCodage(): void
    {
        $this->checkPermRead();

        $praticien_id  = CView::get('praticien_id', 'ref class|CMediusers');
        $codable_class = CView::get('codable_class', 'str');
        $codable_id    = CView::get('codable_id', 'ref meta|codable_class');
        $date          = CView::get('date', 'date');
        $lock          = CView::get('lock', 'bool default|1');
        $export        = CView::get('export', 'bool default|0');

        CView::checkin();

        $user = CMediusers::get();
        /** @var CCodable $codable */
        $codable = CCodable::loadFromGuid("$codable_class-$codable_id");
        if (!$date) {
            $date = CMbDT::date($codable->_datetime);
        }
        $codage = CCodageCCAM::get($codable, $praticien_id, 1, $date);

        if (CAppUI::gconf("dPccam codage lock_codage_ccam") != 'password' && $codable_class != 'CSejour') {
            $codage                = new CCodageCCAM();
            $codage->praticien_id  = $praticien_id;
            $codage->codable_class = $codable_class;
            $codage->codable_id    = $codable_id;
            $codages               = $codage->loadMatchingList();

            foreach ($codages as $_codage) {
                $_codage->locked = $lock;
                $_codage->store();
            }

            $msg = $lock ? 'CCodageCCAM-msg-codage_locked' : 'CCodageCCAM-msg-codage_unlocked';
            CAppUI::setMsg($msg, UI_MSG_OK);
            echo CAppUI::getMsg();
            CApp::rip();
        }

        $askPassword = false;
        if (CAppUI::gconf("dPccam codage lock_codage_ccam") == 'password' && $user->_id != $codage->praticien_id) {
            $askPassword = true;
        }

        $this->renderSmarty('inc_check_lock_codage.tpl', [
            'praticien_id' => $praticien_id,
            'praticien' => $codage->loadPraticien(),
            'codable_class' => $codable->_class,
            'codable_id' => $codable->_id,
            'date' => $date,
            'lock' => $lock,
            'export' => $export,
            'askPassword' => $askPassword,
        ]);
    }

    public function lockCodage(): void
    {
        $this->checkPermEdit();

        $codable_class    = CView::post('codable_class', 'str');
        $codable_id       = CView::post('codable_id', 'ref meta|codable_class');
        $praticien_id     = CView::post('praticien_id', 'ref class|CMediusers');
        $date             = CView::post('date', 'date');
        $user_password    = CView::post('user_password', 'str');
        $lock_all_codages = CView::post('lock_all_codages', 'bool default|0');
        $lock             = CView::post('lock', 'bool default|1');
        $export           = CView::post('export', 'bool default|0');

        CView::checkin();

        $codage                = new CCodageCCAM();
        $codage->praticien_id  = $praticien_id;
        $codage->codable_class = $codable_class;
        $codage->codable_id    = $codable_id;
        $codage->date = $date && !$lock_all_codages ? $date : null;

        /** @var CCodageCCAM[] $codages */
        $codages   = $codage->loadMatchingList();
        $user      = CMediusers::get();
        $praticien = CMediusers::get($praticien_id);

        if (
            CAppUI::gconf("dPccam codage lock_codage_ccam") != 'password'
            || (CAppUI::gconf("dPccam codage lock_codage_ccam") == 'password'
                && ($user->_id === $praticien->_id || CUser::checkPassword($praticien->_user_username, $user_password)))
        ) {
            $object = null;
            foreach ($codages as $_codage) {
                $_codage->locked = $lock;
                $result          = $_codage->store();

                if (!$result) {
                    $_codage->loadActesCCAM();
                    $object = $_codage->loadCodable();

                    foreach ($_codage->_ref_actes_ccam as $_act) {
                        $_act->signe           = $lock;
                        $_act->_no_synchro_eai = true;
                        $_act->store();
                    }
                }
            }

            /* Export des actes */
            if ($object && $export && $lock) {
                $is_factured = $object->facture;
                /* If the object is already factured, we must set the field facture to 0, before set it back to 1,
                 * because the acts are only exported if the field factured is modified to 1
                 */
                if ($is_factured) {
                    $object->facture         = '0';
                    $object->_no_synchro_eai = true;
                    $object->store(false);
                }

                $object->_force_sent     = true;
                $object->_no_synchro_eai = false;
                $object->facture         = '1';
                $object->loadLastLog();

                try {
                    $_msg = $object->store(false);

                    $object->loadRefsActesCCAM();
                    foreach ($codages as $_codage) {
                        foreach ($_codage->_ref_actes_ccam as $_act) {
                            $_act->_no_synchro_eai = true;
                            $_act->sent            = 1;
                            $_act->store();
                        }
                    }

                    $object->loadRefsCodagesCCAM();
                    $finished = true;
                    foreach ($object->_ref_codages_ccam as $_codage_by_prat) {
                        foreach ($_codage_by_prat as $_codage) {
                            if (!$_codage->locked) {
                                $finished = false;
                            }
                        }
                    }

                    if (!$finished) {
                        $object->facture         = '0';
                        $object->_no_synchro_eai = true;
                        $object->_force_sent     = false;
                        $object->store(false);
                    }
                } catch (CMbException $e) {
                    // Cas d'erreur on repasse la facturation à l'état précédent
                    if (!$is_factured) {
                        $object->facture         = '0';
                        $object->_no_synchro_eai = true;
                        $object->_force_sent     = false;
                        $object->store(false);
                    }
                }
            } elseif (!$lock && $object->facture) {
                $object->facture         = 0;
                $object->_ref_actes_ccam = null;
                $object->loadRefsActesCCAM();

                foreach ($object->_ref_actes_ccam as $_act) {
                    $_act->sent            = 0;
                    $_act->_no_synchro_eai = true;
                    $_act->store();
                }
            }

            $msg = $lock ? 'CCodageCCAM-msg-codage_locked' : 'CCodageCCAM-msg-codage_unlocked';
            CAppUI::setMsg($msg, UI_MSG_OK);
            echo CAppUI::getMsg();
        } elseif (
            CAppUI::gconf("dPccam codage lock_codage_ccam") == 'password' && $user->_id !== $praticien->_id
            && !CUser::checkPassword($praticien->_user_username, $user_password)
        ) {
            CAppUI::setMsg("CUser-user_password-nomatch", UI_MSG_ERROR);
            echo CAppUI::getMsg();
        }
    }

    public function duplicateCodage(): void
    {
        $this->checkPermRead();

        $codage_id = CView::get('codage_id', 'ref class|CCodageCCAM');
        $acte_id   = CView::get('acte_id', 'ref class|CActeCCAM');

        CView::checkin();

        $codage = new CCodageCCAM();
        $codage->load($codage_id);

        if ($codage->_id) {
            $codage->canDo();
            if (!$codage->_can->edit) {
                CAppUI::accessDenied();
            }

            $codage->loadCodable();
            $codage->loadPraticien()->loadRefFunction();
            $codage->_ref_praticien->isAnesth();
            $codage->loadActesCCAM();
            $codage->checkRules();
            // Chargement du codable et des actes possibles
            $codage->loadCodable();

            foreach ($codage->_ref_actes_ccam as $_acte) {
                $_acte->getTarif();
                $_phase = $_acte->_ref_code_ccam->activites[$_acte->code_activite]->phases[$_acte->code_phase];

                /* Verification des modificateurs codés */
                if (property_exists($_phase, '_modificateurs')) {
                    foreach ($_phase->_modificateurs as $modificateur) {
                        if (strpos($_acte->modificateurs, $modificateur->code) !== false) {
                            $modificateur->_checked = $modificateur->code;
                        } else {
                            $modificateur->_checked = null;
                        }
                    }
                }

                CCodageCCAM::precodeModifiers($_phase->_modificateurs, $_acte, $codage->_ref_codable);
            }

            $this->renderSmarty('inc_duplicate_codage.tpl', [
                'codage'  => $codage,
                'acte_id' => $acte_id,
            ]);
        }
    }

    public function massCoding(): void
    {
        $this->checkPermRead();

        $objects_guid    = CView::post("objects_guid", "str");
        $chir_id         = CView::post("chir_id", "ref class|CMediusers");
        $libelle         = CView::post("libelle", "str");
        $protocole_id    = CView::post("protocole_id", "ref class|CProtocole");
        $model_codage_id = CView::post("model_codage_id", "ref class|CModelCodage");
        $object_class    = CView::post('object_class', 'enum list|COperation|CSejour-seances');

        CView::checkin();

        $chir      = CMediusers::get($chir_id);
        $listChirs = $chir->loadPraticiens(PERM_DENY);

        $codable      = new CCodable();
        $model_codage = new CModelCodage();

        $protocole_id = $protocole_id ? (int)$protocole_id : null;

        if (!$model_codage->load($model_codage_id)) {
            $objects_guid_arr = explode("|", $objects_guid);
            if (count($objects_guid_arr) != 0) {
                /** @var CCodable $object */
                $object                     = CMbObject::loadFromGuid($objects_guid_arr[0]);
                $model_codage->libelle      = $libelle;
                $model_codage->praticien_id = $chir->_id;
                $model_codage->objects_guid = $objects_guid;
                $model_codage->setFromObject($object, $protocole_id);
            }
        }

        $model_codage->_objects_count = count(explode("|", $model_codage->objects_guid));
        $model_codage->loadRefsCodagesCCAM();

        if (array_key_exists($chir_id, $model_codage->_ref_codages_ccam)) {
            $codages = $model_codage->_ref_codages_ccam[$chir_id];
            foreach ($codages as $_codage) {
                $_codage->loadPraticien()->loadRefFunction();
                $_codage->_ref_praticien->isAnesth();
                $_codage->loadActesCCAM();
                $_codage->getTarifTotal();
                $_codage->checkRules();

                foreach ($_codage->_ref_actes_ccam as $_acte) {
                    $_acte->getTarif();
                }

                // Chargement du codable et des actes possibles
                $_codage->loadCodable();
            }
        }

        $model_codage->loadExtCodesCCAM();
        $model_codage->loadRefsActesCCAM();
        $praticien = $model_codage->loadRefPraticien();
        $praticien->loadRefFunction();
        $praticien->isAnesth();
        $model_codage->getActeExecution();
        $model_codage->loadPossibleActes($chir_id);

        $this->renderSmarty('inc_mass_coding.tpl', [
            'subject' => $model_codage,
            'codages' => $codages,
            'praticien' => $praticien,
            'object_class' => $object_class,
            'acte_ngap' => CActeNGAP::createEmptyFor($model_codage),
        ]);
    }

    public function duplicateCodageCcam(): void
    {
        $this->checkPermEdit();

        $codage_id     = CView::post("codage_id", "ref class|CCodageCCAM");
        $actes         = CView::post("actes", "str");
        $date          = CView::post("date", "date");
        $date_multiple = CView::post("multiple_date", "str");
        $type_of_date  = CView::post("type_of_date", "str default|one_date");

        CView::checkin();

        $actes = explode("|", $actes);

        $codage = new CCodageCCAM();

        if ($codage->load($codage_id)) {
            $codage->canDo();

            if (!$codage->_can->edit) {
                CAppUI::accessDenied();
            }

            $codable = $codage->loadCodable();
            $codable->loadExtCodesCCAM();
            $codable->loadRefsActesCCAM();
            $codage->loadActesCCAM();

            /* Compte les actes non codés, triés par code, activité, et phase */
            $uncoded_acts = array();
            $coded_acts = $codable->_ref_actes_ccam;
            foreach ($codable->_ext_codes_ccam as $_code) {
                foreach ($_code->activites as $_activite) {
                    foreach ($_activite->phases as $_phase) {
                        $coded = false;
                        foreach ($coded_acts as $_acte) {
                            if ($_acte->code_acte == $_code->code && !$coded) {
                                if (
                                    $_acte->code_activite == $_activite->numero
                                    && $_acte->code_phase == $_phase->phase
                                ) {
                                    $coded = true;
                                    unset($coded_acts[$_acte->_id]);
                                }
                            }
                        }

                        if (!$coded) {
                            $key = "$_code->code-$_activite->numero-$_phase->phase";
                            $uncoded_acts[$key] = !array_key_exists($key, $uncoded_acts) ? 1 : $uncoded_acts[$key] + 1;
                        }
                    }
                }
            }

            if ($type_of_date != "one_date") {
                $date_multiple = explode(",", $date_multiple);
                $days = count($date_multiple);
            } else {
                $date = CValue::first($date, $codage->_ref_codable->sortie);
                $days = CMbDT::daysRelative($codage->date . ' 00:00:00', CMbDT::format($date, '%Y-%m-%d 00:00:00'));
            }

            for ($i = 1; $i <= $days; $i++) {
                if ($type_of_date != "one_date") {
                    $_date = $date_multiple[$i - 1];
                } else {
                    $_date = CMbDT::date("+$i DAYS", $codage->date);
                }

                $_codage = new CCodageCCAM();
                $_codage->praticien_id = $codage->praticien_id;
                $_codage->codable_class = $codage->codable_class;
                $_codage->codable_id = $codage->codable_id;
                $_codage->date = $_date;

                $_codage->loadMatchingObject();

                if ($codage->association_mode == 'user_choice') {
                    $_codage->association_mode = $codage->association_mode;
                    $_codage->association_rule = $codage->association_rule;
                }

                $_codage->store();

                foreach ($actes as $_acte_id) {
                    if (array_key_exists($_acte_id, $codage->_ref_actes_ccam)) {
                        $_acte = $codage->_ref_actes_ccam[$_acte_id];

                        /* Si il n'y a pas d'acte non coté pour ce code ccam, on l'ajoute au codable */
                        $key = "$_acte->code_acte-$_acte->code_activite-$_acte->code_phase";
                        if (array_key_exists($key, $uncoded_acts)) {
                            $uncoded_acts[$key] = $uncoded_acts[$key] - 1;
                            if ($uncoded_acts[$key] == 0) {
                                unset($uncoded_acts[$key]);
                            }
                        } else {
                            $codable->codes_ccam .= "|$_acte->code_acte";
                            $codable->updateFormFields();
                            $codable->updateCCAMPlainField();
                            $codable->store();
                        }

                        $_acte->execution = "$_date " . CMbDT::time(null, $_acte->execution);
                        $_acte->_ref_object = $codable;
                        $_acte->_id = null;
                        $_acte->store();
                    }
                }
            }
        }
    }

    public function setDentsCodage(): void
    {
        $this->checkPermRead();

        $view = CView::get('acte_view', 'str');
        $code = CView::get('code', 'str');
        $activite = CView::get('activite', 'str');
        $phase = CView::get('phase', 'str');
        $date = CView::get('date', array('dateTime', 'default' =>  CMbDT::dateTime()));
        $nullable = CView::get('nullable', 'bool default|0');

        CView::checkin();

        $acte = new CActeCCAM();
        $acte->code_acte = $code;
        $acte->code_activite = $activite;
        $acte->code_phase;
        $acte->execution = $date;
        $acte->loadRefCodeCCAM();

        $code = CDatedCodeCCAM::get($code, $date);
        $activite = $code->activites[$activite];
        $phase    = $activite->phases[$phase];

        $dents = CDentCCAM::loadList();
        $liste_dents = reset($dents);

        $this->renderSmarty('inc_set_dents.tpl', [
            'acte_view' => $view,
            'acte' => $acte,
            'phase' => $phase,
            'liste_dents' => $liste_dents,
            'nullable' => $nullable,
        ]);
    }

    public function updateActsCounter(): void
    {
        $this->checkPermRead();

        $subject_guid = CView::get('subject_guid', 'guid class|CCodable');
        $type         = CView::get('type', 'str default|ccam');
        CView::checkin();

        $count = 0;
        if ($subject_guid) {
            /** @var CCodable $subject */
            $subject = CMbObject::loadFromGuid($subject_guid);
            switch ($type) {
                case 'ngap':
                    $subject->loadRefsActesNGAP();
                    $count = count($subject->_ref_actes_ngap);
                    break;
                case 'ccam':
                default:
                    $subject->loadRefsActesCCAM();
                    $count = count($subject->_ref_actes_ccam);
            }
        }

        $this->renderSmarty('inc_acts_counter.tpl', [
            'count' => $count,
            'subject_guid' => $subject_guid,
            'type' => $type,
        ]);
    }

    public function updateMontantBase(): void
    {
        $this->checkPermAdmin();

        $date = CView::get('date', 'str');
        $step = CView::get('step', 'num default|100');
        $codable_class = CView::get('codable_class', 'str');

        $start = CView::get('start_update_montant', 'num default|0', true);

        CView::checkin();

        $act = new CActeCCAM();
        $where = array();
        $where['execution'] = " > '$date 00:00:00'";
        $where['code_association'] = ' > 1';

        if ($codable_class) {
            $where['object_class'] = " = '$codable_class'";
        }
        $total = $act->countList($where);
        /** @var CActeCCAM[] $acts */
        $acts = $act->loadList($where, 'execution DESC', "$start, $step");

        foreach ($acts as $_act) {
            $_act->_calcul_montant_base = 1;
            $_act->store();
        }

        CSessionHandler::start();
        CView::setSession('start_update_montant', $start + $step);
        CSessionHandler::writeClose();

        $this->renderSmarty('inc_status_update_montant.tpl', [
            'total' => $total,
            'current' => $start + $step,
        ]);
    }

    public function applyModelCodage(): void
    {
        set_time_limit(300);

        $this->checkPermEdit();

        $model_codage_id = CView::post('model_codage_id', 'ref class|CModelCodage');
        $apply           = CView::post('apply', 'bool default|1');
        $export          = CView::post('export', 'bool default|0');
        $force_message   = CView::post('force_message', 'bool default|0');
        $object_class    = CView::post('object_class', 'enum list|COperation|CSejour-seances');

        CView::checkin();

        $model = new CModelCodage();
        $model->load($model_codage_id);

        $model->loadRefPraticien();
        $model->loadRefsActesCCAM();
        $model->loadRefsActesNGAP();
        $model->loadRefsCodagesCCAM();
        /** @var CCodageCCAM[] $model_codages */
        $model_codages = $model->_ref_codages_ccam[$model->praticien_id];
        $objects       = $model->loadObjects();

        $model_acts = [
            'ccam' => [],
            'ngap' => [],
        ];

        $model_codes = explode('|', $model->codes_ccam);

        foreach ($model->_ref_actes_ccam as $_act) {
            $key = "$_act->code_acte-$_act->code_activite-$_act->code_phase";
            if (!array_key_exists($key, $model_acts)) {
                $model_acts['ccam'][$key] = [];
            }

            $model_acts['ccam'][$key][] = $_act;
        }

        foreach ($model->_ref_actes_ngap as $_act) {
            $model_acts['ngap'][] = $_act;
        }

        $count_operations = 0;

        if ($apply) {
            foreach ($objects as $_object) {
                $_error = 0;
                $_object->loadRefsCodagesCCAM();
                $_object->loadRefsActesCCAM();
                $_object->getActeExecution();
                $_model_acts = $model_acts;

                $_object_codes = $_object->codes_ccam != '' ? explode('|', $_object->codes_ccam) : [];

                $_codes_ccam = [];
                $_diff       = array_diff($_object_codes, $model_codes);
                $_codes_ccam = empty($_object_codes) || empty($_diff) ? $model_codes
                    : array_merge($model_codes, $_diff);

                $_object->codes_ccam  = implode('|', $_codes_ccam);
                $_object->_codes_ccam = $_codes_ccam;

                $msg = $_object->_class == 'COperation' ? $_object->store(false) : $_object->store();

                if ($msg) {
                    continue;
                }

                /* Vérification de l'affectation des dépassements d'honoraires */
                $depassement_affecte        = false;
                $depassement_anesth_affecte = false;
                foreach ($_object->_ref_actes_ccam as $_act) {
                    if ($_act->code_activite == 1 && $_act->montant_depassement) {
                        $depassement_affecte = true;
                    } elseif ($_act->code_activite == 4 && $_act->montant_depassement) {
                        $depassement_anesth_affecte = true;
                    }
                }

                switch ($_object->_class) {
                    case 'COperation':
                        $_date = $_object->date;
                        break;
                    case 'CConsultation':
                        $_object->loadRefPlageConsult();
                        $_date = $_object->_date;
                        break;
                    default:
                        $_date = CMbDT::date();
                }

                foreach ($model_codages as $_model_codage) {
                    $_codage                  = new CCodageCCAM();
                    $_codage->codable_class   = $_object->_class;
                    $_codage->codable_id      = $_object->_id;
                    $_codage->praticien_id    = $_model_codage->praticien_id;
                    $_codage->activite_anesth = $_model_codage->activite_anesth;
                    $_codage->date            = $_date;

                    $_codage->loadMatchingObject();

                    $_codage->association_mode = $_model_codage->association_mode;
                    $_codage->association_rule = $_model_codage->association_rule;

                    if ($_error = $_codage->store()) {
                        break;
                    }
                }

                if ($_error) {
                    continue;
                }

                foreach ($_object->_ref_actes_ccam as $_act) {
                    $key = "$_act->code_acte-$_act->code_activite-$_act->code_phase";

                    if ($_act->executant_id != $model->praticien_id) {
                        continue;
                    }

                    if (!array_key_exists($key, $_model_acts['ccam'])) {
                        if ($_act->montant_depassement && $_act->code_activite == 1) {
                            $depassement_affecte = false;
                        } elseif ($_act->montant_depassement && $_act->code_activite == 4) {
                            $depassement_anesth_affecte = false;
                        }

                        $_act->delete();
                        continue;
                    }

                    /** @var CActeCCAM $_model_act */
                    $_model_act = reset($_model_acts['ccam'][$key]);

                    $_act->code_association = $_model_act->code_association;
                    $_act->code_extension   = $_model_act->code_extension;

                    /* Afftectation des dépassement en priorité aux actes existants */
                    if (!$depassement_affecte && $_act->code_activite == 1) {
                        if ($_object->_acte_depassement) {
                            $_act->montant_depassement = $_object->_acte_depassement;
                            $depassement_affecte       = true;
                        } elseif ($_model_act->montant_depassement) {
                            $_act->montant_depassement = $_model_act->montant_depassement;
                            $depassement_affecte       = true;
                        }
                    } elseif (!$depassement_anesth_affecte && $_act->code_activite == 4) {
                        if ($_object->_acte_depassement_anesth) {
                            $_act->montant_depassement  = $_object->_acte_depassement_anesth;
                            $depassement_anesth_affecte = true;
                        } elseif ($_model_act->montant_depassement) {
                            $_act->montant_depassement  = $_model_act->montant_depassement;
                            $depassement_anesth_affecte = true;
                        }
                    }

                    $_act->motif_depassement      = $_model_act->motif_depassement;
                    $_act->facturable             = $_model_act->facturable;
                    $_act->extension_documentaire = $_model_act->extension_documentaire;
                    $_act->rembourse              = $_model_act->rembourse;
                    $_act->execution              = $_object->_acte_execution;
                    $_act->modificateurs          = $_model_act->modificateurs;
                    $_act->position_dentaire      = $_model_act->position_dentaire;
                    $_act->commentaire            = $_model_act->commentaire;
                    $_act->precodeModifiers();

                    if ($_error = $_act->store()) {
                        break;
                    }

                    if (count($_model_acts['ccam'][$key]) == 1) {
                        unset($_model_acts['ccam'][$key]);
                    } else {
                        unset($_model_acts['ccam'][$key][0]);
                    }
                }

                if ($_error) {
                    continue;
                }

                foreach ($_model_acts['ccam'] as $code => $_acts) {
                    foreach ($_acts as $_model_act) {
                        $_act                   = new CActeCCAM();
                        $_act->object_class     = $_object->_class;
                        $_act->object_id        = $_object->_id;
                        $_act->code_acte        = $_model_act->code_acte;
                        $_act->code_activite    = $_model_act->code_activite;
                        $_act->code_phase       = $_model_act->code_phase;
                        $_act->code_extension   = $_model_act->code_extension;
                        $_act->executant_id     = $_model_act->executant_id;
                        $_act->code_association = $_model_act->code_association;

                        /* Afftectation des dépassement */
                        if (!$depassement_affecte && $_act->code_activite == 1) {
                            if ($_object->_acte_depassement) {
                                $_act->montant_depassement = $_object->_acte_depassement;
                                $depassement_affecte       = true;
                            } elseif ($_model_act->montant_depassement) {
                                $_act->montant_depassement = $_model_act->montant_depassement;
                                $depassement_affecte       = true;
                            }
                        } elseif (!$depassement_anesth_affecte && $_act->code_activite == 4) {
                            if ($_object->_acte_depassement_anesth) {
                                $_act->montant_depassement  = $_object->_acte_depassement_anesth;
                                $depassement_anesth_affecte = true;
                            } elseif ($_model_act->montant_depassement) {
                                $_act->montant_depassement  = $_model_act->montant_depassement;
                                $depassement_anesth_affecte = true;
                            }
                        }

                        $_act->motif_depassement      = $_model_act->motif_depassement;
                        $_act->facturable             = $_model_act->facturable;
                        $_act->extension_documentaire = $_model_act->extension_documentaire;
                        $_act->rembourse              = $_model_act->rembourse;
                        $_act->execution              = $_object->_acte_execution;
                        $_act->modificateurs          = $_model_act->modificateurs;
                        $_act->position_dentaire      = $_model_act->position_dentaire;
                        $_act->commentaire            = $_model_act->commentaire;
                        $_act->precodeModifiers();

                        if ($_act->code_activite == 4) {
                            if (!$_act->extension_documentaire) {
                                $_act->extension_documentaire = $_object->getExtensionDocumentaire($_act->executant_id);
                            }

                            /* Dans le cas des actes d'activité 4,
                               la date d'execution est la même que l'activité 1 si elle est codée */
                            $acte_chir = $_act->loadActeActiviteAssociee();
                            if ($acte_chir->_id) {
                                $_act->execution = $acte_chir->execution;
                                if ($acte_chir->code_extension) {
                                    $_act->code_extension = $acte_chir->code_extension;
                                }
                            }
                        }

                        if ($_error = $_act->store()) {
                            break;
                        }
                    }
                }

                foreach ($_model_acts['ngap'] as $_model_act) {
                    $_act                      = new CActeNGAP();
                    $_act->object_class        = $_object->_class;
                    $_act->object_id           = $_object->_id;
                    $_act->code                = $_model_act->code;
                    $_act->quantite            = $_model_act->quantite;
                    $_act->coefficient         = $_model_act->coefficient;
                    $_act->montant_depassement = $_model_act->montant_depassement;
                    $_act->montant_base        = $_model_act->montant_base;
                    $_act->demi                = $_model_act->demi;
                    $_act->complement          = $_model_act->complement;
                    $_act->executant_id        = $_model_act->executant_id;
                    $_act->lettre_cle          = $_model_act->lettre_cle;
                    $_act->facturable          = $_model_act->facturable;
                    $_act->lieu                = $_model_act->lieu;
                    $_act->exoneration         = $_model_act->exoneration;
                    $_act->gratuit             = $_model_act->gratuit;
                    $_act->qualif_depense      = $_model_act->qualif_depense;
                    $_act->accord_prealable    = $_model_act->accord_prealable;
                    $_act->date_demande_accord = $_model_act->date_demande_accord;
                    $_act->reponse_accord      = $_model_act->reponse_accord;
                    $_act->execution           = $_object->_acte_execution;

                    if ($_error = $_act->store()) {
                        break;
                    }
                }

                if ($_error) {
                    continue;
                }

                if ($export) {
                    $_codage->locked = 1;
                    $_codage->store();

                    $_object->facture     = 1;
                    $_object->_force_sent = true;
                    $_object->loadLastLog();

                    try {
                        $_object->store();

                        $_object->_ref_actes_ccam = null;
                        $_object->loadRefsCodagesCCAM();
                        $_object->loadRefsActesCCAM();

                        foreach ($_object->_ref_actes_ccam as $_act) {
                            $_act->sent = 1;
                            $_act->store();
                        }

                        $finished = true;

                        foreach ($_object->_ref_codages_ccam as $_codage_by_prat) {
                            foreach ($_codage_by_prat as $_codage) {
                                if (!$_codage->locked) {
                                    $finished = false;
                                    break 2;
                                }
                            }
                        }

                        if (!$finished) {
                            $_object->facture         = 0;
                            $_object->_no_synchro_eai = true;
                            $_object->store(false);
                        }
                    } catch (CMbException $e) {
                        // Cas d'erreur on repasse la facturation à l'état précédent
                        $_object->facture = 0;
                        $_object->store();
                        $_error = 1;
                    }
                }

                if (!$_error) {
                    $count_operations++;
                }
            }
        }

        $model->delete();

        $object_traduction = $object_class == 'CSejours-seances' ? 'séances' : 'interventions';

        if ($force_message) {
            CAppUI::stepAjax('CActeNGAP-applied', UI_MSG_OK);
        } elseif (!$apply && !$export) {
            CAppUI::stepAjax('Codage en masse annulé', UI_MSG_OK);
        } elseif ($count_operations == count($objects)) {
            CAppUI::stepAjax("Le codage a été appliqué avec succès à $count_operations $object_traduction", UI_MSG_OK);
        } else {
            $errors = count($objects) - $count_operations;
            CAppUI::stepAjax(
                "Le codage a été appliqué avec succès à $count_operations $object_traduction,"
                . " $errors $object_traduction marquées en erreurs.",
                UI_MSG_WARNING
            );
        }
    }

    /**
     * @throws Exception
     */
    public function showChooseDate(): void
    {
        $this->checkPermRead();
        $codage_guid  = CView::get('codage_id', 'guid');
        $codable_guid = CView::get("codable_guid", 'guid');

        CView::checkin();
        $codable = null;

        if ($codable_guid) {
            $codable = CMbObject::loadFromGuid($codable_guid);
        }

        $codage = CMbObject::loadFromGuid($codage_guid);
        if ($codage->_id && $codage instanceof CCodageCCAM) {
            $codage->loadCodable();
        }
        $this->renderSmarty("choose_date_duplication.tpl", [
            "codage"  => $codage,
            "codable" => $codable,
        ]);
    }
}
