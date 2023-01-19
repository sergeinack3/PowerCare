<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\Adapter\MySqlAdapter;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Mapper\MapperInterface;
use Ox\Import\Framework\Mapper\MapperMetadata;

/**
 * Description
 */
class MySQLMapperBuilder implements MapperBuilderInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    private const DSN = 'maidis_import';

    /** @var CSQLDataSource */
    private $ds;

    public function __construct()
    {
        $this->ds = CSQLDataSource::get(self::DSN);
    }

    public function build(string $name): MapperInterface
    {
        $adapter = new MySqlAdapter($this->ds);

        $conditions = [];

        switch ($name) {
            case 'utilisateur':
                $meta   = new MapperMetadata('SEUSER', 'USER_ID', $this->configuration);
                $mapper = new UtilisateurMapper($meta, $adapter);
                break;

            case 'patient':
                if ($patient_id = $this->configuration['patient_id']) {
                    $conditions['PATIENT_ID'] = $this->ds->prepare('= ?', $patient_id);
                }

                $meta   = new MapperMetadata('EXPPATIENT', 'PATIENT_ID', $this->configuration, $conditions);
                $mapper = new PatientMapper($meta, $adapter);
                break;

            case 'medecin':
                $meta   = new MapperMetadata('COCORRESP', 'CORRESP_ID', $this->configuration);
                $mapper = new MedecinMapper($meta, $adapter);
                break;

            case 'correspondant_medical':
                if ($patient_id = $this->configuration['patient_id']) {
                    $conditions['PATIENT_ID'] = $this->ds->prepare('= ?', $patient_id);
                }

                $meta   = new MapperMetadata(
                    'PAPATIENT_CORRESP',
                    'PATIENTCORRESP_ID',
                    $this->configuration,
                    $conditions
                );
                $mapper = new CorrespondantMapper($meta, $adapter);
                break;

            case 'plage_consultation':
                $meta   = new MapperMetadata('PACONTACT', 'CONTACT_ID', $this->configuration);
                $mapper = new PlageConsultationMapper($meta, $adapter);
                break;

            case 'consultation':
                $mapper = $this->buildConsultationMapper($adapter);
                break;

            case 'solde_patient':
                if (!CModule::getActive('galaxie')) {
                    throw new ImportException('Module Galaxie is not active');
                }

                if ($patient_id = $this->configuration['patient_id']) {
                    $conditions['PATIENT_ID'] = $this->ds->prepare('= ?', $patient_id);
                }

                $conditions['SOLDE'] = '> 0';

                $meta   = new MapperMetadata('MOPATIENTSOLDE', 'PATIENTSOLDE_ID', $this->configuration, $conditions);
                $mapper = new GalaxieInfosPatientMapper($meta, $adapter);
                break;

            case 'document':
                $mapper = $this->buildFileMapper($adapter);
                break;

            default:
                throw new ImportException('Mapper ' . $name . ' not implemented');
        }

        if ($this->configuration) {
            $adapter->setConfiguration($this->configuration);
        }

        if ($mapper instanceof ConfigurableInterface && $this->configuration) {
            $mapper->setConfiguration($this->configuration);
        }

        return $mapper;
    }

    private function buildConsultationMapper(MySqlAdapter $adapter): MapperInterface
    {
        $ljoin = [
            'SEOBJECT'  => 'PACONSULTATION.OBJECT_ID = SEOBJECT.OBJECT_ID',
            'PACONTACT' => 'SEOBJECT.CONTACT_ID = PACONTACT.CONTACT_ID',
        ];

        $conditions = [
            'SEOBJECT.OBJECTTYPE_ID' => "= '7'",
            'PACONTACT.CONTACT_ID'   => 'IS NOT NULL',
        ];

        if ($patient_id = $this->configuration['patient_id']) {
            $conditions['SEOBJECT.PATIENT_ID'] = "= '$patient_id'";
        }

        $adapter->setLJoin($ljoin);

        $meta = new MapperMetadata('PACONSULTATION', 'CONTACT_ID', $this->configuration, $conditions);

        return new ConsultationMapper($meta, $adapter);
    }

    private function buildFileMapper(MySqlAdapter $adapter): MapperInterface
    {
        $conditions = [
            'PAMI.MITEXT' => '!= "MIGRATION"',
            'PAMI.MIDATE' => 'IS NOT NULL',
        ];

        if ($patient_id = $this->configuration['patient_id']) {
            $conditions['PAMI.PATIENT_ID'] = "= '$patient_id'";
        }

        $select = [
            'MI_ID',
            'OBJECT_ID',
            'PATIENT_ID',
            'MIDATE',
            'USER_ID',
            'GROUP_CONCAT(`MITEXT` SEPARATOR "\n") AS MITEXT',
        ];

        $group = ['OBJECT_ID'];

        $meta = new MapperMetadata('PAMI', 'MI_ID', $this->configuration, $conditions, $select, $group);

        return new FileMapper($meta, $adapter);
    }
}
