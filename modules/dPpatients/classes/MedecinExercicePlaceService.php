<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

class MedecinExercicePlaceService
{
    /** @var CMbObject */
    protected $context;

    /** @var string */
    protected $field_medecin;

    /** @var string */
    protected $field_exercice;

    public function __construct(CStoredObject $context, string $field_medecin, string $field_exercice)
    {
        $this->context        = $context;
        $this->field_medecin  = $field_medecin;
        $this->field_exercice = $field_exercice;
    }

    public function applyFirstExercicePlace(): void
    {
        $this->context->completeField($this->field_medecin, $this->field_exercice);

        // Si le médecin change, on vide le lieu d'exercice
        if ($this->context->fieldModified($this->field_medecin) || !$this->context->{$this->field_medecin}) {
            $this->context->{$this->field_exercice} = '';

            // Si pas de médecin, pas de traitement
            if (!$this->context->{$this->field_medecin}) {
                return;
            }
        }

        // Si un lieu d'exercice est déjà présent, pas de traitement
        if ($this->context->{$this->field_exercice}) {
            // Est-ce que le lieu d'exercice existe réellement ? Cas lors de la suppression d'un lieu d'exercice saisi
            // à la main qui ne vide pas les références
            $medecin_exercice_place = new CMedecinExercicePlace();
            $medecin_exercice_place->load($this->context->{$this->field_exercice});
            if ($medecin_exercice_place->_id) {
                return;
            }

            $this->context->{$this->field_exercice} = '';
        }

        /** @var CMedecin $medecin */
        $medecin = $this->context->loadFwdRef($this->field_medecin);

        $exercice_places = $medecin->getMedecinExercicePlaces();

        /** @var CMedecinExercicePlace $_exercice_place */
        foreach ($exercice_places as $_exercice_place) {
            if (!$_exercice_place->adeli && $_exercice_place->exercice_place_id) {
                $this->context->{$this->field_exercice} = $_exercice_place->_id;
                break;
            }
        }
    }
}
