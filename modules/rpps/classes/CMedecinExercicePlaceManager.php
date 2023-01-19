<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\MedecinExercicePlaceService;

/**
 * Description
 */
class CMedecinExercicePlaceManager
{
    /** @var array */
    private $errors = [];

    /** @var array */
    private $infos = [];

    public function removeOldMedecinExercicePlaces(int $count = 100): void
    {
        $days = CAppUI::conf('rpps disable_days_withtout_update');
        if (!$days) {
            return;
        }

        $med_ex_ids = $this->loadMedecinExercicePlacesToDisable((int)$days, $count);

        if (!$med_ex_ids) {
            return;
        }

        $med_ex_place = new CMedecinExercicePlace();
        $count        = count($med_ex_ids);
        if ($msg = $med_ex_place->deleteAll($med_ex_ids)) {
            $this->errors[] = $msg;
        }

        $this->infos[] = CAppUI::tr('CMedecinExercicePlaceManager-Msg-Old exercice place disabled', $count);
    }

    public function disableMedecinsWithoutExercicePlace(int $count = 100): void
    {
        $medecins = $this->loadMedecinsWithoutExercicePlace($count);

        if (!$medecins) {
            return;
        }

        $count_disable = 0;
        /** @var CMedecin $_med */
        foreach ($medecins as $_med) {
            $_med->actif = '0';
            if ($msg = $_med->store()) {
                $this->errors[] = $msg;
                continue;
            }

            $count_disable++;
        }

        $this->infos[] = CAppUI::tr('CMedecinExercicePlaceManager-Msg-CMedecins disabled', $count_disable);
    }

    private function loadMedecinExercicePlacesToDisable(int $days, int $count): array
    {
        $med_ex_place = new CMedecinExercicePlace();
        $ds           = $med_ex_place->getDS();

        $where = [
            'rpps_file_version' => $ds->prepare('< ?', CMbDT::date("-{$days} DAY")),
            '`rpps_file_version` IS NOT NULL',
            '`rpps_file_version` != ""',
        ];

        return $med_ex_place->loadIds($where, null, $count);
    }

    private function loadMedecinsWithoutExercicePlace(int $count): array
    {
        $medecin = new CMedecin();
        $ljoin   = [
            'medecin_exercice_place' => '`medecin_exercice_place`.medecin_id = `medecin`.medecin_id',
        ];

        $where = [
            '`medecin_exercice_place`.medecin_exercice_place_id IS NULL',
            '`medecin`.actif = "1"',
        ];

        return $medecin->loadList($where, null, $count, null, $ljoin);
    }

    /**
     * Cette fonction permet de traiter le cas où des medecin exercice place ont été créé par erreur lors de la
     * synchronisation
     * @throws CImportMedecinException
     * @throws Exception
     */
    public function removeBadMatchingMedecinExercicePlace(CMedecin $medecin): void
    {
        if (!$medecin->rpps) {
            return;
        }

        $medecin_exercices_places = $medecin->getMedecinExercicePlaces();
        /** @var CMedecinExercicePlace $_medecin_exercice_place */
        foreach ($medecin_exercices_places as $_medecin_exercice_place) {
            if ($_medecin_exercice_place->adeli) {
                $backProps = [
                    'dest_items'              => ['adresse_par_prat_id', 'adresse_par_exercice_place_id'],
                    'consultations_adresses'  => ['object_id', 'medecin_exercice_place_id'],
                    'correspondants_courrier' => ['object_id', 'medecin_exercice_place_id'],
                    'patients_correspondants' => ['medecin_id', 'medecin_exercice_place_id'],
                    'sejours_adresses'        => ['adresse_par_prat_id', 'adresse_par_exercice_place_id'],
                    'patients_traites'        => ['medecin_traitant', 'medecin_traitant_exercice_place_id'],
                ];

                // On vient clean les relations avant de delete le lieu d'exercice
                foreach ($backProps as $_back_prop => $values) {
                    foreach ($_medecin_exercice_place->loadBackRefs($_back_prop) as $_object) {
                        $_object->{$values[1]} = '';
                        (new MedecinExercicePlaceService($_object, $values[0], $values[1]))->applyFirstExercicePlace();
                        $_object->rawStore();
                    }
                }

                if ($msg = $_medecin_exercice_place->delete()) {
                    throw new CImportMedecinException($msg);
                }
            }
        }
    }

    public function getInfos(): array
    {
        return $this->infos;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
