<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;

class CSlotLegacyController extends CLegacyController
{
    public function modalReplaySlot(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        $this->renderSmarty('vw_replay_slot');
    }

    public function replaySlot(): void
    {
        $this->checkPermAdmin();

        $start = CView::get("start", "num default|0");

        CView::checkin();

        CApp::setTimeLimit(300);
        CApp::setMemoryLimit("2048M");

        $plage_consult  = new CPlageconsult();
        $step           = 1000;
        $limit          = "$start,$step";
        $where          = ["date" => ">= '2021-01-01'"];
        $plages_consult = $plage_consult->loadList($where, null, $limit);
        $insert         = [];
        $ds             = CSQLDataSource::get("std");

        CStoredObject::massLoadBackRefs($plages_consult, "slots");
        foreach ($plages_consult as $_plage_consult) {
            $slots_by_datetime = [];
            $_plage_consult->loadRefsSlots();
            $date           = CMbDT::date($_plage_consult->date);
            $datetime_start = $date . " " . CMbDT::time($_plage_consult->debut);
            $datetime_end   = $date . " " . CMbDT::time($_plage_consult->fin);
            foreach ($_plage_consult->_ref_slots as $_slot) {
                if (!isset($slots_by_datetime[$_slot->start])) {
                    $slots_by_datetime[$_slot->start] = [];
                }
                $slots_by_datetime[$_slot->start][$_slot->_id] = $_slot;
            }
            while ($datetime_start < $datetime_end) {
                $datetime_end_slot = CMbDT::addDateTime($_plage_consult->freq, $datetime_start);

                //Si aucun slot pour ce créneau => on en crée un dispo
                if (!isset($slots_by_datetime[$datetime_start])) {
                    $insert[] = [
                        "plageconsult_id" => $_plage_consult->_id,
                        "start"           => $datetime_start,
                        "end"             => $datetime_end_slot,
                    ];
                } else {
                    //Si un slot busy existe pour ce créneau on vérifie les surréservation
                    ksort($slots_by_datetime[$datetime_start]);
                    $first_slot = reset($slots_by_datetime[$datetime_start]);
                    if ($first_slot->status == "busy") {
                        $first_slot->overbooked = 0;
                        $first_slot->rawStore();
                    }
                }

                $datetime_start = $datetime_end_slot;
            }
        }

        if (!empty($insert)) {
            $ds->insertMulti("slot", $insert, 1000);
        }

        $this->renderJson(["countPlage" => count($plages_consult)]);
    }

    /**
     * Show the modal to correct consultation to slot
     *
     * @return void
     * @throws Exception
     */
    public function modalReplayConsultationToSlot(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        $this->renderSmarty('vw_replay_consultation_slot');
    }

    /**
     * Script to correct consultation to slot
     *
     * @return void
     * @throws Exception
     */
    public function replayConsultationToSlot(): void
    {
        $this->checkPermAdmin();

        $start = CView::get("start", "num default|0");

        CView::checkin();

        CApp::setTimeLimit(300);
        CApp::setMemoryLimit("2048M");

        $ds    = CSQLDataSource::get("std");
        $step  = 1000;
        $limit = "$start,$step";
        $where = ["date" => ">= '2021-01-01'"];

        $request = new CRequest();
        $request->addSelect("plageconsult_id, date, debut, fin, freq");
        $request->addTable("plageconsult");
        $request->addWhere($where);
        $request->setLimit($limit);
        $plages_consult = $ds->loadList($request->makeSelect());
        $insert         = [];

        foreach ($plages_consult as $_plage_consult) {
            $request = new CRequest();
            $request->addSelect("annule, heure, duree, consultation_id");
            $request->addTable("consultation");
            $request->addWhere(
                [
                    "plageconsult_id" => "= " . $_plage_consult['plageconsult_id'],
                    "annule"          => "!= '1'",
                ]
            );
            $consultations = $ds->loadList($request->makeSelect());

            foreach ($consultations as $_consultation) {
                $date_debut = $_plage_consult["date"] . " " . $_consultation["heure"];
                $heure_fin  = CMbDT::addTime($_consultation["heure"], $_plage_consult["freq"]);
                $date_fin   = $_plage_consult["date"] . " " . $heure_fin;
                for ($i = 1; $i <= $_consultation["duree"]; $i++) {
                    $request = new CRequest();
                    $request->addSelect("slot_id");
                    $request->addTable("slot");
                    $request->addWhere(
                        [
                            "plageconsult_id" => "= '" . $_plage_consult['plageconsult_id'] . "'",
                            "consultation_id" => "= '" . $_consultation["consultation_id"] . "'",
                            "start"           => "= '$date_debut'",
                            "end"             => "= '$date_fin'",
                        ]
                    );
                    $slot_id = $ds->loadResult($request->makeSelect());

                    //La consultation est déjà relié à un slot
                    if ($slot_id) {
                        $date_debut = $date_fin;
                        $heure_fin  = CMbDT::addTime($heure_fin, $_plage_consult["freq"]);
                        $date_fin   = $_plage_consult["date"] . " " . $heure_fin;
                        continue;
                    }

                    $request = new CRequest();
                    $request->addSelect("slot_id, consultation_id");
                    $request->addTable("slot");
                    $request->addWhere(
                        [
                            "plageconsult_id" => "= " . $_plage_consult['plageconsult_id'],
                            "start"           => "= '$date_debut'",
                            "end"             => "= '$date_fin'",
                            "consultation_id" => "IS NULL",
                        ]
                    );
                    $slot = $ds->loadHash($request->makeSelect());

                    if ($slot && $slot["slot_id"]) {
                        //Lier la consultation à un slot free
                        $query = $ds->prepare(
                            "UPDATE `slot`
                                        SET consultation_id = ?1, status = 'busy'
                                        WHERE slot_id = ?2;",
                            $_consultation["consultation_id"],
                            $slot["slot_id"]
                        );
                        if (!$ds->exec($query)) {
                            trigger_error(
                                "Erreur store slot (" . $_consultation['consultation_id'] . ", " . $_plage_consult['plageconsult_id'] . ")"
                            );
                        }
                    } else {
                        //Lier la consultation à un slot overbooked
                        $insert[] = [
                            "plageconsult_id" => $_plage_consult["plageconsult_id"],
                            "consultation_id" => $_consultation["consultation_id"],
                            "start"           => $date_debut,
                            "end"             => $date_fin,
                            "overbooked"      => true,
                            "status"          => "busy",
                        ];
                    }

                    $date_debut = $date_fin;
                    $heure_fin  = CMbDT::addTime($heure_fin, $_plage_consult["freq"]);
                    $date_fin   = $_plage_consult["date"] . " " . $heure_fin;
                }
            }
        }

        if (!empty($insert)) {
            $ds->insertMulti("slot", $insert, 1000);
        }

        $this->renderJson(["countPlage" => count($plages_consult)]);
    }
}
