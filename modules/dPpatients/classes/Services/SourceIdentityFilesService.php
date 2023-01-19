<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Services;

use DateInterval;
use DateTimeImmutable;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Patients\CPatient;

class SourceIdentityFilesService
{
    /** @var string The string used to create the DateInterval object representing the duration of the validity of a file */
    private const VALIDITY_PERIOD = 'P5Y';

    /** @var DateTimeImmutable */
    private $date_time;

    /**
     * @param DateTimeImmutable|null $date_time
     */
    public function __construct(DateTimeImmutable $date_time = null)
    {
        if (!$date_time) {
            $date_time = new DateTimeImmutable();
        }

        $this->date_time = $date_time->sub(new DateInterval(self::VALIDITY_PERIOD));
    }

    /**
     * @param int $start
     * @param int $limit
     *
     * @return CPatient[]
     */
    public function getPatientsWithExpiredFiles(int $start = 0, int $limit = 20): array
    {
        $limit_clause = null;
        if ($limit > 0) {
            $limit_clause = "$start, $limit";
        }

        $patients = (new CPatient())->loadList([
            "EXISTS (" . $this->getFilesQuery() . ")",
            "NOT EXISTS (" . $this->getSejoursQuery() . ")",
            "NOT EXISTS (" . $this->getConsultationsQuery() . ")",
        ], 'nom ASC, prenom ASC', $limit_clause, 'patients.patient_id', [
           '`source_identite` AS i ON i.`patient_id` = patients.`patient_id`'
        ]);

        if (!is_array($patients)) {
            $patients = [];
        }

        return $patients;
    }

    public function countPatientsWithExpiredFiles(): int
    {
        $result = (new CPatient())->countList([
            "EXISTS (" . $this->getFilesQuery() . ")",
            "NOT EXISTS (" . $this->getSejoursQuery() . ")",
            "NOT EXISTS (" . $this->getConsultationsQuery() . ")",
        ], null, [
           '`source_identite` AS i ON i.`patient_id` = `patients`.`patient_id`'
        ]);

        return is_null($result) ? 0 : (int)$result;
    }

    /**
     * @param CPatient[] $patients
     *
     * @return array
     */
    public function deleteExpiredIdentityFilesForPatients(array $patients): array
    {
        $sources = CMbObject::massLoadBackRefs($patients, 'sources_identite');
        CMbObject::massLoadBackRefs($sources, 'files');

        $results = [
            'errors'  => [],
            'success' => 0,
        ];
        foreach ($patients as $patient) {
            $sources = $patient->loadRefsSourcesIdentite(false);
            foreach ($sources as $source) {
                $source->loadRefJustificatif();

                if ($source->_ref_justificatif && $source->_ref_justificatif->_id) {
                    if ($msg = $source->_ref_justificatif->delete()) {
                        $results['errors'][] = "{$patient->_view}: $msg";
                    } else {
                        $results['success']++;

                        /* If the source is the main source for the patient, it's status is set to PROV */
                        if (
                            $patient->source_identite_id === $source->_id
                            && in_array($patient->status, ['VALI', 'QUAL'])
                        ) {
                            $patient->status = 'PROV';
                            $patient->store();
                        }

                        $source->identity_proof_type_id = '';
                        $source->store();
                    }
                }
            }
        }

        return $results;
    }

    private function getFilesQuery(): string
    {
        return (new CRequest())
            ->addSelect('1')
            ->addTable('`files_mediboard` AS f')
            ->addWhereClause('f.object_class', " = 'CSourceIdentite'")
            ->addWhereClause('f.object_id', " = i.`source_identite_id`")
            ->setLimit('0, 1')
            ->makeSelect();
    }

    private function getSejoursQuery(): string
    {
        return (new CRequest())
            ->addSelect('1')
            ->addTable('`sejour` AS s')
            ->addWhereClause('s.patient_id', " = patients.`patient_id`")
            ->addWhereClause(null, "DATE(s.sortie)  > '" . $this->date_time->format('Y-m-d') . "'")
            ->setLimit('0, 1')
            ->makeSelect();
    }

    private function getConsultationsQuery(): string
    {
        return (new CRequest())
            ->addSelect('1')
            ->addTable('`consultation` AS c')
            ->addLJoin('`plageconsult` AS pl ON pl.`plageconsult_id` = c.`plageconsult_id`')
            ->addWhereClause('c.patient_id', " = patients.`patient_id`")
            ->addWhereClause('pl.date', " > '" . $this->date_time->format('Y-m-d') . "'")
            ->setLimit('0, 1')
            ->makeSelect();
    }
}
