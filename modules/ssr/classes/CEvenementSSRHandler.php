<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Handler sur les séjours ssr et psy
 */
class CEvenementSSRHandler extends ObjectHandler
{
    static $handled = ["CSejour", "CPrescriptionLineElement"];

    /**
     * @inheritdoc
     */
    static function isHandled(CStoredObject $object)
    {
        if (!CModule::getActive("ssr") && !CModule::getActive("psy")) {
            return null;
        }

        return in_array($object->_class, self::$handled);
    }

    /**
     * @inheritdoc
     */
    function onAfterStore(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return;
        }

        if ($object instanceof CSejour) {
            /* @var CSejour $object */
            if ((!$object->fieldModified("entree") && !$object->fieldModified("sortie") && !$object->fieldModified(
                    "annule"
                )
                || !$object->countBackRefs("evenements_ssr"))
            ) {
                return;
            }
            $this->cleanEvtsSejour($object);
        } else {
            /* @var CPrescriptionLineElement $object */
            if (!$object->fieldModified("date_arret") || !$object->date_arret
                || !in_array($object->_ref_element_prescription->_ref_category_prescription->chapitre, ["kine", "psy"])
                || $object->_ref_prescription->object_class != "CSejour"
                || !in_array($object->_ref_prescription->_ref_object->type, ["ssr", "psy"])
                || !CAppUI::gconf("ssr soins delete_evts_line_stop")
            ) {
                return;
            }

            $this->cleanEvtsHorsLineElement($object);
        }
    }

    /**
     * Purge des événements futurs hors de la ligne de prescription
     *
     * @param CPrescriptionLineElement $object Object handled
     *
     * @return void
     */
    function cleanEvtsHorsLineElement(CPrescriptionLineElement $object)
    {
        //Recherche des événements hors de la ligne de prescription
        $where              = [];
        $where["sejour_id"] = "= '" . $object->_ref_prescription->object_id . "'";
        $where["debut"]     = " >= '" . CMbDT::date("+1 DAY", $object->date_arret) . "'";
        $where["realise"]   = " = '0'";
        $evenement          = new CEvenementSSR();
        $evenements         = $evenement->loadList($where);

        //Purge des événements
        foreach ($evenements as $_evenement) {
            $msg = $_evenement->purge();
            CAppUI::displayMsg($msg, "CEvenementSSR-msg-purge_hors_line_elt");
        }
    }


    /**
     * Purge des événements futurs hors séjour
     *
     * @param CSejour $object Object handled
     *
     * @return void
     */
    function cleanEvtsSejour(CSejour $object)
    {
        //Recherche des événements hors séjour
        $where              = [];
        $where["sejour_id"] = "= '$object->_id'";
        if (!$object->annule) {
            $date_min = CMbDT::date($object->entree);
            $date_max = CMbDT::date("+1 DAY", $object->sortie);
            $where[]  = "(debut NOT BETWEEN '$date_min' AND '$date_max') OR debut IS NULL";
        }
        $evenement  = new CEvenementSSR();
        $evenements = $evenement->loadList($where);
        // Purge des événements hors séjour
        foreach ($evenements as $_evenement) {
            if ($_evenement->plage_groupe_patient_id && !$_evenement->debut) {
                $plage_groupe = $_evenement->loadRefPlageGroupePatient();
                $debut        = CMbDT::date("$plage_groupe->groupe_day this week") . " " . $plage_groupe->heure_debut;

                if ($debut >= $date_min && $debut <= $date_max) {
                    continue;
                }
            }

            $msg = $_evenement->purge();
            CAppUI::displayMsg($msg, "CEvenementSSR-msg-purge_hors_sejour");
        }

        //Recherche de l'ensemble des évènements ssr collectif ayant été planifié via la trame de planning collectif
        if (!($object->fieldModified("sortie") && $object->sortie > $object->_old->sortie)) {
            return;
        }

        /*
         * Récupération des événements planifiées avec une plage collective durant les 7 derniers jours de la sortie initiale
         * Ce sont ces événéments qui seront dupliqués juqu'à la nouvelle date de sortie
        */
        $date_search_min               = CMbDT::date("-7 days", CMbDT::date($object->_old->sortie));
        $where                         = [];
        $where["sejour_id"]            = " = '$object->_id'";
        $where["type_seance"]          = " = 'collective'";
        $where["seance_collective_id"] = " IS NOT NULL";
        $where["plage_id"]             = "IS NOT NULL";
        $where["debut"]                = " >= '$date_search_min 00:00:00'";
        $evenements                    = $evenement->loadList($where);

        $where                            = [];
        $where["sejour_id"]               = " = '$object->_id'";
        $where["type_seance"]             = " <> 'collective'";
        $where["seance_collective_id"]    = " IS NULL";
        $where["plage_groupe_patient_id"] = "IS NOT NULL";
        $where["debut"]                   = " >= '$date_search_min 00:00:00'";
        $evenements_groupe                = $evenement->loadList($where);

        $date_sortie = CMbDT::date($object->sortie);
        foreach ($evenements as $_evenement) {
            /* @var CEvenementSSR $seance_coll */
            $seance_coll = $_evenement->loadRefSeanceCollective();

            $datetime_new_evt = CMbDT::dateTime("+7 days", $seance_coll->debut);
            for (
                $dateTime = $datetime_new_evt; CMbDT::date($dateTime) <= $date_sortie; $dateTime = CMbDT::dateTime(
                "+7 days",
                $dateTime
            )
            ) {
                if (CMbDT::date($datetime_new_evt) > $date_sortie) {
                    continue;
                }
                $seance_coll->_id   = null;
                $seance_coll->debut = $dateTime;
                $seance_coll->loadMatchingObject();

                //Création de la séance collective si elle n'existe pas
                if (!$seance_coll->_id) {
                    $msg = $seance_coll->store();
                    CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");
                }

                //Création de l'événement ssr du patient
                if ($seance_coll->_id) {
                    $evt                               = new CEvenementSSR();
                    $evt->sejour_id                    = $object->_id;
                    $evt->plage_id                     = $_evenement->plage_id;
                    $evt->debut                        = $dateTime;
                    $evt->duree                        = $_evenement->duree;
                    $evt->therapeute_id                = $_evenement->therapeute_id;
                    $evt->therapeute2_id               = $_evenement->therapeute2_id;
                    $evt->therapeute3_id               = $_evenement->therapeute3_id;
                    $evt->prescription_line_element_id = $_evenement->prescription_line_element_id;
                    $evt->seance_collective_id         = $seance_coll->_id;
                    $evt->type_seance                  = "collective";
                    $evt->loadMatchingObject();
                    if (!$evt->_id) {
                        if ($msg = $evt->store()) {
                            CAppUI::setMsg($msg, UI_MSG_ERROR);
                            continue;
                        }
                        $_evenement->duplicateBackRefs($_evenement, "actes_csarr", $evt->_id);
                        $_evenement->duplicateBackRefs($_evenement, "prestas_ssr", $evt->_id);
                        CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");
                    }
                }
            }
        }

        // Group range
        foreach ($evenements_groupe as $_evenement_groupe) {
            $line_elt       = $_evenement_groupe->loadRefPrescriptionLineElement();
            $datetime_event = CMbDT::dateTime("+7 days", $_evenement_groupe->debut);
            for (
                $dateTime = $datetime_event; CMbDT::date($dateTime) <= $date_sortie; $dateTime = CMbDT::dateTime(
                "+7 days",
                $dateTime
            )
            ) {
                if (CMbDT::date($datetime_event) > $date_sortie) {
                    continue;
                }

                // Ne pas dépasser la fin réelle de la ligne d'élément
                if (CMbDT::date($datetime_event) > CMbDT::date($line_elt->_fin_reelle)) {
                    continue;
                }

                $event                               = new CEvenementSSR();
                $event->sejour_id                    = $object->_id;
                $event->plage_id                     = $_evenement_groupe->plage_id;
                $event->debut                        = $dateTime;
                $event->duree                        = $_evenement_groupe->duree;
                $event->therapeute_id                = $_evenement_groupe->therapeute_id;
                $event->therapeute2_id               = $_evenement_groupe->therapeute2_id;
                $event->therapeute3_id               = $_evenement_groupe->therapeute3_id;
                $event->prescription_line_element_id = $_evenement_groupe->prescription_line_element_id;
                $event->type_seance                  = $_evenement_groupe->type_seance;
                $event->plage_groupe_patient_id      = $_evenement_groupe->plage_groupe_patient_id;
                $event->loadMatchingObject();

                if (!$event->_id) {
                    if ($msg = $event->store()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                        continue;
                    }

                    $_evenement_groupe->duplicateBackRefs($_evenement_groupe, "actes_csarr", $event->_id);
                    CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    function onAfterMerge(CStoredObject $object)
    {
        $this->onAfterStore($object);
    }
}
