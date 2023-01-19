<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\MedecinFieldService;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

class CMedecinLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function listMedecins(): void
    {
        $this->checkPermRead();

        $dialog      = CValue::get("dialog");
        $view_update = CValue::get("view_update");

        // Parametre de tri
        $order_way = CValue::getOrSession("order_way", "DESC");
        $order_col = CValue::getOrSession("order_col", "ccmu");

        // Mode annuaire
        $annuaire = CValue::get("annuaire", 0);

        // pagination
        $start_med = CValue::get("start_med", 0);
        $step_med  = CValue::get("step_med", 20);

        $medecin = new CMedecin();
        $ds      = $medecin->getDS();

        // Récuperation des médecins recherchés
        if ($dialog) {
            $medecin_nom         = CView::get("medecin_nom", "str");
            $medecin_prenom      = CView::get("medecin_prenom", "str");
            $medecin_function_id = CView::get("function_id", "ref class|CFunctions");
            $medecin_cp          = CView::get("medecin_cp", "numchar");
            $medecin_ville       = CView::get("medecin_ville", "str");
            $medecin_type        = CView::get(
                "type",
                "enum list|"
                . implode('|', CMedecin::$types)
                . "|pharmacie|maison_medicale|autre default|medecin"
            );
            $medecin_disciplines = CView::get("disciplines", "text");
            $actif               = CView::get("actif", "enum list|0|1|2 default|1");
            $rpps                = CView::get("rpps", "numchar", true);
        } else {
            $medecin_nom         = CView::get("medecin_nom", "str", true);
            $medecin_prenom      = CView::get("medecin_prenom", "str", true);
            $medecin_function_id = CView::get("function_id", "ref class|CFunctions", true);
            $medecin_cp          = CView::get("medecin_cp", "numchar", true);
            $medecin_ville       = CView::get("medecin_ville", "str", true);
            $medecin_type        = CView::get(
                "type",
                "enum list|"
                . implode('|', CMedecin::$types)
                . "|pharmacie|maison_medicale|autre default|medecin",
                true
            );
            $medecin_disciplines = CView::get("disciplines", "text", true);
            $actif               = CView::get("actif", "enum list|0|1|2 default|1", true);
            $rpps                = CView::get("rpps", "numchar", true);
        }

        CView::checkin();

        $where = [];

        $current_user = CMediusers::get();
        $is_admin     = $current_user->isAdmin();

        if ($annuaire) {
            // Cas de la consultation en annuaire
            $where["function_id"] = "IS NULL";
            if (CAppUI::isGroup()) {
                $where["group_id"] = "IS NULL";
            }
        } elseif ($medecin_function_id && $is_admin) {
            // Cas de la consultation en administrateur, filtré sur une fonction
            $where["function_id"] = "= '$medecin_function_id'";
        } elseif (CAppUI::isCabinet() && !$is_admin) {
            // Cas du cloisonnement en non administrateur
            $where["function_id"] = "= '$current_user->function_id'";
        } elseif (CAppUI::isGroup() && !$is_admin) {
            $where["group_id"] = "= '" . $current_user->loadRefFunction()->group_id . "'";
        }

        if ($rpps && strlen($rpps) === 11) {
            $where["rpps"] = $ds->prepareLike($rpps);
        } else {
            if ($medecin_nom) {
                $medecin_nom  = stripslashes($medecin_nom);
                $where["nom"] = $ds->prepareLike("$medecin_nom%");
            }

            if ($medecin_prenom) {
                $where["prenom"] = $ds->prepareLike("%$medecin_prenom%");
            }

            if ($medecin_disciplines) {
                $where_disciplines   = [];
                $where_disciplines[] = "medecin.disciplines LIKE '%" . $medecin_disciplines . "%'";
                $where_disciplines[] = "medecin_exercice_place.disciplines LIKE '%" . $medecin_disciplines . "%'";
                $where[]             = implode(" OR ", $where_disciplines);
            }

            if ($actif !== '2') {
                $where["actif"] = "= '$actif'";
            }

            if ($medecin_cp && $medecin_cp != "00") {
                $cps = preg_split("/\s*[\s\|,]\s*/", $medecin_cp);
                CMbArray::removeValue("", $cps);

                $where_cp = [];
                foreach ($cps as $cp) {
                    $where_cp[] = "exercice_place.cp LIKE '" . $cp . "%'";

                    // La recherche sur les codes postaux implique un full scan de la table
                    // on ne les ajoute que si on recherche au moins sur un nom ou un prénom
                    if ($medecin_nom || $medecin_prenom) {
                        $where_cp[] = "medecin.cp LIKE '" . $cp . "%'";
                    }
                }

                $where[] = implode(" OR ", $where_cp);
            }

            if ($medecin_ville) {
                $where_ville   = [];
                $where_ville[] = "commune LIKE '%" . $medecin_ville . "%'";
                $where_ville[] = "ville LIKE '%" . $medecin_ville . "%'";
                $where[]       = implode(" OR ", $where_ville);
            }

            if ($medecin_type) {
                $where_type   = [];
                $where_type[] = "medecin.type = '" . $medecin_type . "'";
                $where_type[] = "medecin_exercice_place.type = '" . $medecin_type . "'";
                $where[]      = implode(" OR ", $where_type);
            }
        }

        // On crée une jointure car les données peuvent être stockées sur un medecin, un lieu d'exercice ou un medecin exercice place
        $ljoin = [
            'medecin_exercice_place' => 'medecin_exercice_place.medecin_id = medecin.medecin_id',
            'exercice_place'         => 'exercice_place.exercice_place_id = medecin_exercice_place.exercice_place_id',
        ];

        $medecin        = new CMedecin();
        $count_medecins = count($medecin->countMultipleList($where, null, "medecin.medecin_id", $ljoin));
        $order          = "nom, prenom";
        /** @var CMedecin[] $medecins */
        $medecins = $medecin->loadList($where, $order, "$start_med, $step_med", "medecin.medecin_id", $ljoin);

        CStoredObject::massLoadFwdRef($medecins, "function_id");
        CStoredObject::massLoadFwdRef($medecins, "group_id");

        foreach ($medecins as $key => $_medecin) {
            if (!$rpps && ($medecin_cp || $medecin_ville || $medecin_type || $medecin_disciplines)) {
                $_medecin->getExercicePlacesByFilters(
                    $medecin_cp,
                    $medecin_ville,
                    $medecin_type,
                    $medecin_disciplines
                );

                // Si il n'y a pas d'exercice place, on vient alors filtrer sur les champs du médecin
                if (empty($_medecin->_ref_exercice_places)) {
                    if (!str_contains(CMbString::lower($_medecin->ville), CMbString::lower($medecin_ville))) {
                        unset($medecins[$key]);
                        break;
                    }

                    if (!str_contains(CMbString::lower($_medecin->type), CMbString::lower($medecin_type))) {
                        unset($medecins[$key]);
                        break;
                    }

                    if (
                        !str_contains(
                            CMbString::lower($_medecin->disciplines),
                            CMbString::lower($medecin_disciplines)
                        )
                    ) {
                        unset($medecins[$key]);
                        break;
                    }
                }
            } else {
                $_medecin->getExercicePlaces();
            }
            foreach ($_medecin->_ref_exercice_places as $key_place => $_excercice_place) {
                if ($_excercice_place->annule) {
                    unset($_medecin->_ref_exercice_places[$key_place]);
                }
            }
            foreach ($_medecin->_ref_medecin_exercice_places as $key_exercice => $_medecin_exercice) {
                if ($_medecin_exercice->annule) {
                    unset($_medecin->_ref_medecin_exercice_places[$key_exercice]);
                }
            }
        }

        $list_types = $medecin->_specs['type']->_locales;

        $this->renderSmarty(
            'inc_list_medecins',
            [
                "is_admin"       => $is_admin,
                "dialog"         => $dialog,
                "annuaire"       => $annuaire,
                "nom"            => $medecin_nom,
                "prenom"         => $medecin_prenom,
                "cp"             => $medecin_cp,
                "type"           => $medecin_type,
                "medecins"       => $medecins,
                "medecin"        => $medecin,
                "list_types"     => $list_types,
                "count_medecins" => $count_medecins,
                "order_col"      => $order_col,
                "order_way"      => $order_way,
                "start_med"      => $start_med,
                "step_med"       => $step_med,
                "view_update"    => $view_update,
            ]
        );
    }

    public function editMedecin(): void
    {
        $this->checkPermEdit();

        $medecin_id      = CView::get('medecin_id', 'ref class|CMedecin');
        $duplicate       = CView::get('duplicate', 'bool default|0');
        $medecin_type    = CView::get('medecin_type', 'str');
        $compte_rendu_id = CView::get('compte_rendu_id', 'ref class|CCompteRendu');

        CView::checkin();

        $medecin = CMedecin::findOrNew($medecin_id);

        if ($duplicate) {
            $medecin->_id = null;
        }

        if ($medecin->_id) {
            $current_user = CMediusers::get();
            $is_admin     = $current_user->isAdmin();
            if (CAppUI::isCabinet()) {
                $same_function = $current_user->function_id == $medecin->function_id;
                if (!$is_admin && !$same_function) {
                    CAppUI::accessDenied();
                }
            } elseif (CAppUI::isGroup()) {
                $same_group = $current_user->loadRefFunction()->group_id == $medecin->group_id;
                if (!$is_admin && !$same_group) {
                    CAppUI::accessDenied();
                }
            }
        }
        if (!$medecin->_id && !$medecin->type) {
            $medecin->type = $medecin_type;
        }

        $compte_rendu = CCompteRendu::findOrNew($compte_rendu_id);
        // Chargement des formules de politesse selon le praticien du contexte
        $user_id = CMediusers::get()->_id;
        $compte_rendu->loadTargetObject();

        switch (true) {
            case $compte_rendu->_ref_object instanceof CConsultation:
                $user_id = CConsultation::find($compte_rendu->object_id)->loadRefPraticien()->_id;
                break;
            case $compte_rendu->_ref_object instanceof CConsultAnesth:
                $user_id = CConsultAnesth::find($compte_rendu->object_id)->chir_id;
                break;
            case $compte_rendu->_ref_object instanceof COperation:
                $user_id = COperation::find($compte_rendu->object_id)->chir_id;
                break;
            case $compte_rendu->_ref_object instanceof CSejour:
                $user_id = CSejour::find($compte_rendu->object_id)->praticien_id;
                break;

            default:
        }

        $medecin->loadSalutations($user_id);
        $medecin->loadRefsNotes();
        $medecin->loadRefUser();
        $medecin->getExercicePlaces();

        $this->renderSmarty(
            'inc_edit_medecin',
            [
                'object'    => $medecin,
                'spec_cpam' => CSpecCPAM::getList(),
            ]
        );
    }

    /**
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function listMedecinExercicePlaces(): void
    {
        $this->checkPermRead();

        $medecin_id = CView::get('medecin_id', 'ref class|CMedecin');

        CView::checkin();

        $medecin = CMedecin::findOrFail($medecin_id);

        /** @var CMedecinExercicePlace $_medecin_exercice_place */
        foreach ($medecin->getMedecinExercicePlaces() as $_medecin_exercice_place) {
            $_medecin_exercice_place->loadRefExercicePlace();
        }
        $this->renderSmarty('inc_list_medecin_exercice_places', ['medecin' => $medecin]);
    }

    public function chooseExercicePlace(): void
    {
        $medecin_id   = CView::get('medecin_id', 'ref class|CMedecin');
        $object_class = CView::get('object_class', 'str notNull');
        $object_id    = CView::get('object_id', 'ref class|CStoredObject meta|object_class');
        $field        = CView::get('field', 'str');

        CView::checkin();

        $medecin = CMedecin::findOrFail($medecin_id);
        $medecin->getExercicePlaces();

        $object = $object_class::findOrNew($object_id);

        $this->renderSmarty(
            'inc_choose_medecin_exercice_place',
            [
                'medecin'          => $medecin,
                'object'           => $object,
                'field'            => $field,
                'submit_on_change' => 0,
            ]
        );
    }

    /**
     * @return void
     */
    public function tooltipMedecin(): void
    {
        $this->checkPerm();

        $medecin_id                = CView::get('medecin_id', 'ref class|CMedecin');
        $medecin_exercice_place_id = CView::get('medecin_exercice_place_id', 'ref class|CMedecinExercicePlace');

        CView::checkin();

        $medecin                = CMedecin::findOrFail($medecin_id);
        $medecin_exercice_place = CMedecinExercicePlace::findOrFail($medecin_exercice_place_id);

        $medecin->canDo();
        $medecin->loadView();

        $medecin_service      = new MedecinFieldService($medecin, $medecin_exercice_place);
        $medecin->disciplines = $medecin_service->getDisciplines();
        $medecin->tel         = $medecin_service->getTel();
        $medecin->adresse     = $medecin_service->getAdresse();
        $medecin->fax         = $medecin_service->getFax();
        $medecin->cp          = $medecin_service->getCP();
        $medecin->ville       = $medecin_service->getVille();
        $medecin->portable    = $medecin_service->getPortable();
        $medecin->email       = $medecin_service->getEmail();

        $this->renderSmarty('CMedecin_view', ['object' => $medecin]);
    }

    public function listPraticiens(): void
    {
        $this->checkPermAdmin();
        $praticien_id    = CView::get("praticien_id", "str", true);
        $function_select = CView::get("function_select", "str");
        CView::checkin();

        // load all the users from the group
        $praticiens = (new CMediusers())->loadListFromType(
            null,
            PERM_READ,
            ($function_select !== 'all') ? $function_select : null,
            null,
            false
        );

        $this->renderSmarty(
            'inc_vw_export_patients_praticiens',
            [
                'praticiens'         => $praticiens,
                'array_praticien_id' => is_array($praticien_id) ? $praticien_id : (($praticien_id) ? explode(
                    ',',
                    $praticien_id
                ) : []),
            ]
        );
    }
}
