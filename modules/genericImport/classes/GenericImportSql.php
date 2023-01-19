<?php

/**
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
use Ox\Import\GenericImport\Mapper\GenericSqlMapperBuilder;
use Ox\Import\GenericImport\Matcher\GenericMatcher;
use Ox\Import\GenericImport\Persister\GenericPersister;

/**
 * Manager for sql generic import
 */
class GenericImportSql extends CFwImport
{
    public const DATA_SOURCE_1  = 'genericImport1';
    public const DATA_SOURCE_2  = 'genericImport2';
    public const DATA_SOURCE_3  = 'genericImport3';
    public const DATA_SOURCE_4  = 'genericImport4';
    public const DATA_SOURCE_5  = 'genericImport5';
    public const DATA_SOURCE_6  = 'genericImport6';
    public const DATA_SOURCE_7  = 'genericImport7';
    public const DATA_SOURCE_8  = 'genericImport8';
    public const DATA_SOURCE_9  = 'genericImport9';
    public const DATA_SOURCE_10 = 'genericImport10';

    public const DATA_SOURCES = [
        self::DATA_SOURCE_1,
        self::DATA_SOURCE_2,
        self::DATA_SOURCE_3,
        self::DATA_SOURCE_4,
        self::DATA_SOURCE_5,
        self::DATA_SOURCE_6,
        self::DATA_SOURCE_7,
        self::DATA_SOURCE_8,
        self::DATA_SOURCE_9,
        self::DATA_SOURCE_10,
    ];

    public function getImportOrder(): array
    {
        $order        = GenericImport::IMPORT_ORDER;
        $import_order = [];
        foreach ($order as $type) {
            $import_order[$type] = $this->count($type);
        }

        return $import_order;
    }

    /**
     * @throws Exception
     */
    public function getConfiguration(array $additionnal_confs = []): Configuration
    {
        $configs = parent::getConfiguration($additionnal_confs);

        // DSN config
        $configs['dsn'] = CAppUI::conf('genericImport db dsn', $this->campaign);

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

        return $configs;
    }

    protected function getMapperBuilderInstance(): MapperBuilderInterface
    {
        return new GenericSqlMapperBuilder();
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
        return GenericImport::UTILISATEUR;
    }
}
