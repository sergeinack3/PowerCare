<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\FileUtil\CMbCalendar;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

class CalendarExporter
{
    private CMbCalendar $calendar;

    private bool $anonymize;

    private string $date;

    private string $debut;

    private string $fin;

    private CMediusers $praticien;

    private array $context_to_export;

    private string $group;

    private bool $details;

    private array $plagesOp;

    private array $plagesConsult;

    /**
     * @throws Exception
     */
    public function __construct(
        string $date,
        int $weeks_before,
        int $weeks_after,
        CMediusers $praticien,
        array $context_to_export,
        string $group,
        bool $details,
        bool $anonymize
    ) {
        $this->date      = $date;
        $this->praticien = $praticien;
        $this->praticien->needsEdit();
        $this->context_to_export = $context_to_export;
        $this->group             = $group;
        $this->details           = $details;
        $this->anonymize         = $anonymize;
        $this->debut             = CMbDT::date("-$weeks_before week", $this->date);
        $this->debut             = CMbDT::date("last sunday", $this->debut);
        $this->fin               = CMbDT::date("+$weeks_after week", $this->date);
        $this->fin               = CMbDT::date("next sunday", $this->fin);

        $this->calendar = new CMbCalendar(CAppUI::tr("Planning"));
    }


    /**
     * @throws Exception
     */
    public function exportCalendar(bool $stream = true): void
    {
        if (in_array("consult", $this->context_to_export)) {
            $this->loadPlageConsult();
            $this->addPlagesConsultsToCalendar();
        }
        if (in_array("interv", $this->context_to_export)) {
            $this->loadPlageOperatoires();
            $this->addPlagesOperatoiresToCalendar();
        }

        // Conversion du calendrier en champ texte
        $str = $this->calendar->createCalendar();

        if ($stream) {
            $this->streamCalendar($str);
        }
    }

    private function getEventDetails(CPlageOp $rdv, bool $anonymize, string $description): string
    {
        foreach ($rdv->_ref_operations as $op) {
            if ($op->annulee) {
                continue;
            }

            $op->loadRefPatient();
            $op->loadRefPlageOp();
            $duration = CMbDT::format($op->temp_operation, '%Hh%M');
            $when     = CMbDT::format(CMbDT::time($op->_datetime), '%Hh%M');

            $what = ($op->libelle) ?: CAppUI::tr($op->_class);

            if (!$anonymize) {
                $what .= " {$op->_ref_patient->_view}";
            }

            $description .= "\n$when: $what (duree: $duration)";
        }

        return $description;
    }

    /**
     * @throws Exception
     */
    private function loadPlageOperatoires(): void
    {
        $salle = new CSalle();
        /** @var CSalle[] $listSalles */
        $listSalles = $salle->loadGroupList();
        $plageOp    = new CPlageOp();

        for ($i = 0; CMbDT::date("+$i day", $this->debut) != $this->fin; $i++) {
            $date = CMbDT::date("+$i day", $this->debut);
            if (in_array("interv", $this->context_to_export)) {
                $this->plagesOp[$date] = $plageOp->loadPlagesPerDayOp($listSalles, $this->praticien->_id, $date);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function loadPlageConsult(): void
    {
        $plageConsult = new CPlageconsult();
        /** @var CPlageconsult[] $plagesConsult */

        for ($i = 0; CMbDT::date("+$i day", $this->debut) != $this->fin; $i++) {
            $date                       = CMbDT::date("+$i day", $this->debut);
            $this->plagesConsult[$date] = $plageConsult->loadPlagesPerDayConsult($this->praticien->_id, $date);
        }
    }

    /**
     * @throws Exception
     */
    private function addPlagesConsultsToCalendar(): void
    {
        foreach ($this->plagesConsult as $_date => $plagesPerDay) {
            if (!$plagesPerDay) {
                continue;
            }

            switch ($this->group) {
                case '0':
                    foreach ($plagesPerDay as $plage) {
                        $description = $this->parsePlageConsultDescription($plage);

                        $deb = "$plage->date $plage->debut";
                        $fin = "$plage->date $plage->fin";
                        $this->calendar->addEvent(
                            "",
                            "Consultation - $plage->libelle",
                            $plage->_guid,
                            $deb,
                            $fin,
                            $description
                        );
                    }
                    break;

                case '1':
                    $deb = "$_date " . min(CMbArray::pluck($plagesPerDay, 'debut'));
                    $fin = "$_date " . max(CMbArray::pluck($plagesPerDay, 'fin'));

                    $_guid = 'CPlageconsult-' . implode('-', CMbArray::pluck($plagesPerDay, '_id'));

                    [$description, $summary] = $this->parseListePlagesDescription($plagesPerDay);

                    $this->calendar->addEvent("", $summary, $_guid, $deb, $fin, $description);
                    break;

                default:
            }
        }
    }

    /**
     * @throws Exception
     */
    private function addPlagesOperatoiresToCalendar(): void
    {
        switch ($this->group) {
            case '0':
                foreach ($this->plagesOp as $salle) {
                    foreach ($salle as $_plages) {
                        /** @var CPlageOp $rdv */
                        foreach ($_plages as $rdv) {
                            $description = $this->parsePlageOpDescription($rdv);

                            $deb = "$rdv->date $rdv->debut";
                            $fin = "$rdv->date $rdv->fin";

                            $location = $rdv->_ref_salle->_ref_bloc->_ref_group->_view;
                            $this->calendar->addEvent(
                                $location,
                                $rdv->_ref_salle->_view,
                                $rdv->_guid,
                                $deb,
                                $fin,
                                $description
                            );
                        }
                    }
                }
                break;

            case '1':
                foreach ($this->plagesOp as $_salle_id => $salle) {
                    $_salle = new CSalle();
                    $_salle->load($_salle_id);

                    foreach ($salle as $plagesPerDay => $_plages) {
                        $deb   = "$plagesPerDay " . min(CMbArray::pluck($_plages, 'debut'));
                        $fin   = "$plagesPerDay " . max(CMbArray::pluck($_plages, 'fin'));
                        $_guid = 'CPlageOp-' . implode('-', CMbArray::pluck($_plages, '_id'));

                        [$summary, $description] = $this->parsePlagesOpDescription($_plages, $_salle);

                        $location = $_salle->loadRefBloc()->loadRefGroup()->_view;
                        $this->calendar->addEvent($location, $summary, $_guid, $deb, $fin, $description);
                    }
                }
                break;
            default:
                // Do nothing
        }
    }

    private function parsePlageConsultDescription(CPlageconsult $plage_consult): string
    {
        $description = "$plage_consult->_nb_patients patient(s)";
        if ($this->details) {
            foreach ($plage_consult->_ref_consultations as $consult) {
                if ($consult->annule) {
                    continue;
                }
                $when = CMbDT::format($consult->heure, '%Hh%M');

                if ($this->anonymize) {
                    $what = ($consult->motif) ?: CAppUI::tr($consult->_class);
                } else {
                    $patient = $consult->loadRefPatient();
                    $what    = $patient->_id ?
                        "$patient->_civilite $patient->nom"
                        : "Pause: $consult->motif";
                }
                $description .= "\n$when: $what";
            }
        }

        return $description;
    }

    private function parseListePlagesDescription(array $plagesPerDay): array
    {
        $summary     = '';
        $description = '';
        foreach ($plagesPerDay as $_plage) {
            $_debut = CMbDT::format($_plage->debut, '%Hh%M');
            $_fin   = CMbDT::format($_plage->fin, '%Hh%M');

            $summary .= "\n[$_debut - $_fin] " . ($_plage->libelle) ?: CAppUI::tr($_plage->_class);

            /** @var CConsultation $_consult */
            foreach ($_plage->_ref_consultations as $_consult) {
                if ($_consult->annule) {
                    continue;
                }

                $description .= "\n[" . CMbDT::format($_consult->heure, '%Hh%M') . '] ';

                if (!$_consult->patient_id) {
                    $description .= 'PAUSE';
                } else {
                    $description .= (($_consult->motif) ?: CAppUI::tr($_consult->_class));

                    if ($this->details && !$this->anonymize) {
                        $patient     = $_consult->loadRefPatient();
                        $description .= " : $patient->_civilite $patient->nom";
                    }
                }
            }
        }

        return [trim($description), trim($summary)];
    }

    /**
     * @param CPlageOp $rdv
     *
     * @return string
     */
    private function parsePlageOpDescription(CPlageOp $rdv): string
    {
        $description = "$rdv->_count_operations intervention(s)";

        // Evènement détaillé
        if ($this->details) {
            $description = $this->getEventDetails($rdv, $this->anonymize, $description);
        }

        return $description;
    }

    /**
     * @param mixed  $_plages
     * @param CSalle $_salle
     *
     * @return array
     */
    private function parsePlagesOpDescription(array $_plages, CSalle $_salle): array
    {
        $summary     = '';
        $description = '';

        foreach ($_plages as $rdv) {
            $_debut = CMbDT::format($rdv->debut, '%Hh%M');
            $_fin   = CMbDT::format($rdv->fin, '%Hh%M');

            $summary .= "\n[$_debut - $_fin] $_salle->_view";

            // Evènement détaillé
            if ($this->details) {
                $description = $this->getEventDetails($rdv, $this->anonymize, $description);
            }
        }

        return [trim($summary), trim($description)];
    }

    private function streamCalendar(string $str): void
    {
        header("Content-disposition: attachment; filename=agenda.ics");
        header("Content-Type: text/calendar; charset=" . CApp::$encoding);
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Content-Length: " . strlen($str));

        echo $str;
    }
}
