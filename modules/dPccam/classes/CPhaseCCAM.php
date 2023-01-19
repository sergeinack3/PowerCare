<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

/**
 * Class CPhaseCCAM
 * Table p_phase
 *
 * Phase des actes
 * Niveau activite
 */
class CPhaseCCAM extends CCCAM
{
    public $code_phase;
    public $nb_dents;
    public $age_min;
    public $age_max;
    public $icr;
    public $classant;

    // Références
    /** @var  CPhaseInfoCCAM[] */
    public $_ref_classif;
    /** @var  CPhaseDentIncompCCAM[] */
    public $_ref_dents_incomp;
    // Elements de référence pour la récupération d'informations
    public $_code;
    public $_activite;

    /**
     * Mapping des données depuis la base de données
     *
     * @param array $row Ligne d'enregistrement de de base de données
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->code_phase = $row["PHASE"];
        $this->nb_dents   = $row["NBDENTS"];
        $this->age_min    = $row["AGEMIN"];
        $this->age_max    = $row["AGEMAX"];
        $this->icr        = $row["ICR"];
        $this->classant   = $row["CLASSANT"];
    }

    /**
     * Chargement de a liste des phases pour une activite
     *
     * @param string $code     Code CCAM
     * @param string $activite Activité CCAM
     *
     * @return self[] Liste des phases
     */
    public static function loadListFromCodeActivite(string $code, string $activite): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_phase.*
      FROM p_phase
      WHERE p_phase.CODEACTE = %1
      AND p_phase.ACTIVITE = %2
      ORDER BY p_phase.PHASE ASC";
        $query  = $ds->prepare($query, $code, $activite);
        $result = $ds->exec($query);

        $list_phases = [];
        while ($row = $ds->fetchArray($result)) {
            $phase            = new CPhaseCCAM();
            $phase->_code     = $code;
            $phase->_activite = $activite;
            $phase->map($row);
            $list_phases[$row["PHASE"]] = $phase;
        }

        return $list_phases;
    }

    /**
     * Chargement des informations historisées de la phase
     * Table p_phase_acte
     *
     * @return array La liste des informations historisées
     */
    public function loadRefInfo(): array
    {
        return $this->_ref_classif = CPhaseInfoCCAM::loadListFromCodeActivitePhase(
            $this->_code,
            $this->_activite,
            $this->code_phase
        );
    }

    /**
     * Chargement des dents incompatibles de la phase
     * Table p_phase_dentsincomp
     *
     * @return array La liste des informations historisées
     */
    public function loadRefDentsIncomp(): array
    {
        return $this->_ref_dents_incomp =
            CPhaseDentIncompCCAM::loadListFromCodeActivitePhase($this->_code, $this->_activite, $this->code_phase);
    }
}
