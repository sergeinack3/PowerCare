<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use Exception;
use Ox\Core\CAppUI;
use Ox\Import\Framework\CFwImport;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\AbstractPersister;
use Ox\Import\GenericImport\Mapper\GenericCsvMapperBuilder;
use Ox\Import\GenericImport\Matcher\GenericMatcher;
use Ox\Import\GenericImport\Persister\GenericPersister;

class GenericImport extends CFwImport
{
    public const ACTE_CCAM             = 'acte_ccam';
    public const ACTE_NGAP             = 'acte_ngap';
    public const ANTECEDENT            = 'antecedent';
    public const CONSULTATION          = 'consultation';
    public const PLAGE_CONSULTATION    = 'plage_consultation';
    public const CONSTANTE             = 'constante';
    public const CORRESPONDANT_MEDICAL = 'correspondant_medical';
    public const DOSSIER_MEDICAL       = 'dossier_medical';
    public const EVENEMENT             = 'evenement';
    public const INJECTION             = 'injection';
    public const FICHIER               = 'fichier';
    public const MEDECIN               = 'medecin';
    public const PATIENT               = 'patient';
    public const TRAITEMENT            = 'traitement';
    public const UTILISATEUR           = 'utilisateur';
    public const SEJOUR                = 'sejour';
    public const AFFECTATION           = 'affectation';
    public const OPERATION             = 'operation';

    public const OBSERVATION_RESULT        = 'observation_result';
    public const OBSERVATION_RESULT_SET    = 'observation_result_set';
    public const OBSERVATION_RESULT_VALUE  = 'observation_result_value';
    public const OBSERVATION_IDENTIFIER    = 'observation_identifier';
    public const OBSERVATION_ABNORMAL_FLAG = 'observation_abnormal_flag';
    public const OBSERVATION_VALUE_UNIT    = 'observation_value_unit';
    public const OBSERVATION_FILE          = 'observation_file';
    public const OBSERVATION_RESPONSIBLE   = 'observation_responsible';
    public const OBSERVATION_EXAM          = 'observation_exam';
    public const OBSERVATION_PATIENT       = 'observation_patient';

    public const AVAILABLE_TYPES = [
        self::ACTE_CCAM,
        self::ACTE_NGAP,
        self::ANTECEDENT,
        self::CONSULTATION,
        self::CONSTANTE,
        self::CORRESPONDANT_MEDICAL,
        self::DOSSIER_MEDICAL,
        self::EVENEMENT,
        self::INJECTION,
        self::FICHIER,
        self::MEDECIN,
        self::PATIENT,
        self::TRAITEMENT,
        self::UTILISATEUR,
        self::SEJOUR,
        self::AFFECTATION,
        self::OPERATION,
    ];

    public const IMPORT_ORDER = [
        self::PATIENT,
        self::CORRESPONDANT_MEDICAL,
        self::ANTECEDENT,
        self::TRAITEMENT,
        self::CONSTANTE,
        self::DOSSIER_MEDICAL,
        self::SEJOUR,
        self::AFFECTATION,
        self::CONSULTATION,
        self::OPERATION,
        self::EVENEMENT,
        self::INJECTION,
        self::ACTE_CCAM,
        self::ACTE_NGAP,
        self::FICHIER,
    ];

    protected function getMapperBuilderInstance(): MapperBuilderInterface
    {
        return new GenericCsvMapperBuilder();
    }

    protected function getMatcherInstance(): MatcherVisitorInterface
    {
        return new GenericMatcher();
    }

    protected function getPersisterInstance(): AbstractPersister
    {
        return new GenericPersister();
    }

    protected function getUserTable(): string
    {
        return self::UTILISATEUR;
    }

    public function getImportOrder(): array
    {
        $import_order = [];
        $mapped_files = $this->campaign->getMappedFiles();

        foreach (self::IMPORT_ORDER as $type) {
            $import_order[$type] = in_array($type, $mapped_files) ? null : 0;
        }

        return $import_order;
    }

    /**
     * @param array $additionnal_confs
     *
     * @return Configuration
     * @throws Exception
     */
    public function getConfiguration(array $additionnal_confs = []): Configuration
    {
        $configs = parent::getConfiguration($additionnal_confs);

        $import_files = $this->campaign->loadBackRefs('import_files');

        foreach ($import_files as $import_file) {
            $configs = $this->addFilePathConf($configs, $import_file);
        }

        // External files directory
        $configs['external_files_path']
            = CAppUI::conf('genericImport import_files external_files_path', $this->campaign);
        $configs['external_files_replacement_path']
            = CAppUI::conf('genericImport import_files external_files_replacement_path', $this->campaign);
        $configs['find_existing_files']
            = CAppUI::conf('genericImport import_files find_existing_files', $this->campaign);

        // Plage consult start/end
        $configs['plageconsult_heure_debut']
            = CAppUI::conf('genericImport import_dates plageconsult_heure_debut', $this->campaign);
        $configs['plageconsult_heure_fin']
            = CAppUI::conf('genericImport import_dates plageconsult_heure_fin', $this->campaign);

        // Generate IPP
        $configs['generate_ipp']
            = CAppUI::conf('genericImport interop generate_ipp', $this->campaign);

        // NDA
        $configs['generate_nda']
            = CAppUI::conf('genericImport interop generate_nda', $this->campaign);

        $configs['sanitize_functions'] = [$this->getSanitizeLine()];

        return $configs;
    }

    /**
     * @param Configuration $configs
     * @param CImportFile   $import_file
     *
     * @return Configuration
     */
    private function addFilePathConf(Configuration $configs, CImportFile $import_file): Configuration
    {
        switch ($import_file->entity_type) {
            case self::ACTE_CCAM:
                $configs[self::ACTE_CCAM . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::ACTE_NGAP:
                $configs[self::ACTE_NGAP . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::ANTECEDENT:
                $configs[self::ANTECEDENT . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::CONSULTATION:
                $configs[self::CONSULTATION . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::CORRESPONDANT_MEDICAL:
                $configs['correspondant_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::EVENEMENT:
                $configs[self::EVENEMENT . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::INJECTION:
                $configs[self::INJECTION . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::FICHIER:
                $configs[self::FICHIER . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::MEDECIN:
                $configs[self::MEDECIN . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::PATIENT:
                $configs[self::PATIENT . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::TRAITEMENT:
                $configs[self::TRAITEMENT . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::UTILISATEUR:
                $configs['user_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::CONSTANTE:
                $configs[self::CONSTANTE . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::DOSSIER_MEDICAL:
                $configs[self::DOSSIER_MEDICAL . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::SEJOUR:
                $configs[self::SEJOUR . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::AFFECTATION:
                $configs[self::AFFECTATION . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            case self::OPERATION:
                $configs[self::OPERATION . '_file_path'] = $this->buildFilePath($import_file->file_name);
                break;
            default:
                // Do nothing
        }

        return $configs;
    }

    private function buildFilePath(string $file_name): ?string
    {
        if (!$this->campaign || !$this->campaign->_id) {
            return null;
        }

        $file_manager = new ImportFilesManager($this->campaign);

        return $file_manager->getUploadedFilePath($file_name);
    }

    private function getSanitizeLine(): callable
    {
        return function ($data) {
            return $data === '' ? null : $data;
        };
    }
}
