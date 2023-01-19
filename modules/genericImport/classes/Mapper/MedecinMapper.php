<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotMedecin;

/**
 * Description
 */
class MedecinMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotMedecin::FIELD_ID),
            'nom'         => $this->getValue($row, OxPivotMedecin::FIELD_NOM),
            'prenom'      => $this->getValue($row, OxPivotMedecin::FIELD_PRENOM),
            'sexe'        => $this->getValue($row, OxPivotMedecin::FIELD_SEXE),
            'titre'       => $this->getValue($row, OxPivotMedecin::FIELD_TITRE),
            'email'       => $this->getValue($row, OxPivotMedecin::FIELD_EMAIL),
            'disciplines' => $this->getValue($row, OxPivotMedecin::FIELD_DISCIPLINES),
            'tel'         => $this->getValue($row, OxPivotMedecin::FIELD_TEL)
                ? $this->sanitizeTel($row[OxPivotMedecin::FIELD_TEL]) : null,
            'tel_autre'   => $this->getValue($row, OxPivotMedecin::FIELD_TEL_AUTRE)
                ? $this->sanitizeTel($row[OxPivotMedecin::FIELD_TEL_AUTRE]) : null,
            'adresse'     => $this->getValue($row, OxPivotMedecin::FIELD_ADRESSE),
            'cp'          => $this->getValue($row, OxPivotMedecin::FIELD_CP),
            'ville'       => $this->getValue($row, OxPivotMedecin::FIELD_VILLE),
            'rpps'        => $this->getValue($row, OxPivotMedecin::FIELD_RPPS),
            'adeli'       => $this->getValue($row, OxPivotMedecin::FIELD_ADELI),
        ];

        return Medecin::fromState($map);
    }
}
