<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Persister;

use Ox\Core\CStoredObject;
use Ox\Import\Framework\Exception\PersisterException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Import\Exceptions\CabinetPersisterException;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;

class DefaultPersister extends AbstractPersister
{
    public function persistObject(CStoredObject $object): CStoredObject
    {
        switch (get_class($object)) {
            case CPatient::class:
                return $this->persistPatient($object);
            case CFile::class:
                return $this->persistFile($object);
            case CActeCCAM::class:
                return $this->persistActeCCAM($object);
            case CSejour::class:
                return $this->persistSejour($object);
            case CInjection::class:
                return $this->persistInjection($object);
            default:
                return parent::persistObject($object);
        }
    }

    /**
     * Disable IPP generation and set the obtention_mode to import
     */
    protected function persistPatient(CPatient $patient): CPatient
    {
        $patient->_generate_IPP   = false;
        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_IMPORT;

        return $this->persist($patient);
    }

    /**
     * Check if the _file_path is set
     *
     * @param CFile $file
     *
     * @return CFile
     * @throws PersisterException
     */
    public function persistFile(CFile $file): CFile
    {
        if (!$file->_file_path) {
            throw new PersisterException('PersisterException-File must have a context');
        }

        return $this->persist($file);
    }

    /**
     * Must add the acte_ccam to the consultation and store it before storing the acte itself
     *
     * @param CActeCCAM $acte
     *
     * @return CActeCCAM
     * @throws PersisterException
     */
    public function persistActeCCAM(CActeCCAM $acte): CActeCCAM
    {
        /** @var CConsultation $consult */
        $consult             = $acte->loadFwdRef('object_id', true);
        $consult->codes_ccam = ($consult->codes_ccam)
            ? implode('|', array_merge(explode('|', $consult->codes_ccam), [$acte->code_acte]))
            : $acte->code_acte;

        if ($msg = $consult->store()) {
            throw new PersisterException($msg);
        }

        return $this->persist($acte);
    }

    /**
     * @param CSejour $sejour
     *
     * @return CSejour
     * @throws PersisterException
     */
    protected function persistSejour(CSejour $sejour): CSejour
    {
        $sejour->_generate_NDA = false;

        return $this->persist($sejour);
    }

    /**
     * @param CInjection $injection
     *
     * @return CInjection
     * @throws PersisterException
     */
    protected function persistInjection(CInjection $injection): CInjection
    {
        $injection_ids = [];

        // Check if injection exists and is different from the type of vaccine "Autre"
        if ($injection->_type_vaccin && ($injection->_type_vaccin !== CVaccination::TYPE_VACCINATION_AUTRE) && $injection->recall_age) {
            $ds        = $injection->getDS();

            $ljoin = ["vaccination" => "injection.injection_id = vaccination.injection_id"];
            $where = [
                "patient_id"       => $ds->prepare("= ?", $injection->patient_id),
                "vaccination.type" => $ds->prepare("= ?", $injection->_type_vaccin),
                "recall_age"       => $ds->prepare("= ?", $injection->recall_age),
            ];

            $injection_ids = $injection->loadIds($where, null, null, null, $ljoin);
        }

        if (count($injection_ids) > 0) {
            throw CabinetPersisterException::alreadyVaccinated($injection);
        }

        $injection = $this->persist($injection);

        if ($injection->_id) {
            $vaccination               = new CVaccination();
            $vaccination->injection_id = $injection->_id;
            $vaccination->type         = $injection->_type_vaccin;

            if (!$vaccination->loadMatchingObjectEsc()) {
                if ($msg = $vaccination->store()) {
                    throw new PersisterException($msg);
                }
            }
        }

        return $injection;
    }
}
