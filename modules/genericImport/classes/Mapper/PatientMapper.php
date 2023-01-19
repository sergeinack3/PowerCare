<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotPatient;

/**
 * Description
 */
class PatientMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'          => $this->getValue($row, OxPivotPatient::FIELD_ID),
            'ipp'                  => $this->getValue($row, OxPivotPatient::FIELD_IPP),
            'nom'                  => $this->getValue($row, OxPivotPatient::FIELD_NOM),
            'prenom'               => $this->getValue($row, OxPivotPatient::FIELD_PRENOM),
            'deces'           => $this->getValue($row, OxPivotPatient::FIELD_DECES)
                ? $this->convertToDateTime($row[OxPivotPatient::FIELD_DECES]) : null,
            'naissance'            => $this->getValue($row, OxPivotPatient::FIELD_DATE_NAISSANCE)
                ? $this->convertToDateTime($row[OxPivotPatient::FIELD_DATE_NAISSANCE]) : null,
            'cp_naissance'         => $this->getValue($row, OxPivotPatient::FIELD_CP_NAISSANCE),
            'lieu_naissance'       => $this->getValue($row, OxPivotPatient::FIELD_LIEU_NAISSANCE),
            'nom_jeune_fille'      => $this->getValue($row, OxPivotPatient::FIELD_NOM_NAISSANCE),
            'profession'           => $this->getValue($row, OxPivotPatient::FIELD_PROFESSION),
            'email'                => $this->getValue($row, OxPivotPatient::FIELD_EMAIL),
            'tel'                  => $this->getValue($row, OxPivotPatient::FIELD_TEL)
                ? $this->sanitizeTel($row[OxPivotPatient::FIELD_TEL]) : null,
            'tel2'                 => $this->getValue($row, OxPivotPatient::FIELD_TEL2)
                ? $this->sanitizeTel($row[OxPivotPatient::FIELD_TEL2]) : null,
            'tel_autre'            => $this->getValue($row, OxPivotPatient::FIELD_TEL_AUTRE)
                ? $this->sanitizeTel($row[OxPivotPatient::FIELD_TEL_AUTRE]) : null,
            'adresse'              => $this->getValue($row, OxPivotPatient::FIELD_ADRESSE),
            'cp'                   => $this->getValue($row, OxPivotPatient::FIELD_CP),
            'ville'                => $this->getValue($row, OxPivotPatient::FIELD_VILLE),
            'pays'                 => $this->getValue($row, OxPivotPatient::FIELD_PAYS),
            'matricule'            => $this->getValue($row, OxPivotPatient::FIELD_MATRICULE),
            'sexe'                 => $this->getValue($row, OxPivotPatient::FIELD_SEXE),
            'civilite'             => $this->getValue($row, OxPivotPatient::FIELD_CIVILITE),
            'situation_famille'    => $this->getValue($row, OxPivotPatient::FIELD_MARITAL_STATUS),
            'activite_pro'         => $this->getValue($row, OxPivotPatient::FIELD_PROFESSIONAL_ACTIVITY),
            'rques'                => $this->getValue($row, OxPivotPatient::FIELD_REMARQUES),
            'medecin_traitant'     => $this->getValue($row, OxPivotPatient::FIELD_MEDECIN_TRAITANT),
            'ald'                  => $this->getValue($row, OxPivotPatient::FIELD_ALD),
            'nom_assure'           => $this->getValue($row, OxPivotPatient::FIELD_NOM_ASSURE),
            'prenom_assure'        => $this->getValue($row, OxPivotPatient::FIELD_PRENOM_ASSURE),
            'nom_naissance_assure' => $this->getValue($row, OxPivotPatient::FIELD_NOM_NAISSANCE_ASSURE),
            'sexe_assure'          => $this->getValue($row, OxPivotPatient::FIELD_SEXE_ASSURE),
            'civilite_assure'      => $this->getValue($row, OxPivotPatient::FIELD_CIVILITE_ASSURE),
            'naissance_assure'     => $this->getValue($row, OxPivotPatient::FIELD_NAISSANCE_ASSURE)
                ? $this->convertToDateTime($row[OxPivotPatient::FIELD_NAISSANCE_ASSURE]) : null,
            'adresse_assure'       => $this->getValue($row, OxPivotPatient::FIELD_ADRESSE_ASSURE),
            'ville_assure'         => $this->getValue($row, OxPivotPatient::FIELD_VILLE_ASSURE),
            'cp_assure'            => $this->getValue($row, OxPivotPatient::FIELD_CP_ASSURE),
            'pays_assure'          => $this->getValue($row, OxPivotPatient::FIELD_PAYS_ASSURE),
            'tel_assure'           => $this->getValue($row, OxPivotPatient::FIELD_TEL_ASSURE)
                ? $this->sanitizeTel($row[OxPivotPatient::FIELD_TEL_ASSURE]) : null,
            'matricule_assure'     => $this->getValue($row, OxPivotPatient::FIELD_MATRICULE_ASSURE),
        ];

        return Patient::fromState($map);
    }
}
