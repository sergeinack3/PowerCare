<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis;

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\CFwImport;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Transformer\AbstractTransformer;
use Ox\Import\Maidis\Mapper\MySQLMapperBuilder;
use Ox\Mediboard\Galaxie\Import\Matcher\GalaxieMatcher;
use Ox\Mediboard\Galaxie\Import\Transformer\GalaxieTransformer;

/**
 * Description
 */
class MaidisImport extends CFwImport
{
    public const IMPORT_ORDER = [
        'patient',
        'correspondant_medical',
        'consultation',
        'document',
    ];

    private $galaxie_active = false;

    public function __construct()
    {
        $this->galaxie_active = CModule::getActive('galaxie');
    }

    public function getImportOrder(): array
    {
        $import_types = self::IMPORT_ORDER;

        if ($this->galaxie_active) {
            $import_types = array_merge($import_types, ['solde_patient']);
        }

        $import_order = [];
        foreach ($import_types as $type) {
            $import_order[$type] = null;
        }

        return $import_order;
    }

    protected function getMapperBuilderInstance(): MapperBuilderInterface
    {
        return new MySQLMapperBuilder();
    }

    protected function getUserTable(): string
    {
        return 'utilisateur';
    }

    protected function getMatcherInstance(): MatcherVisitorInterface
    {
        return ($this->galaxie_active) ? new GalaxieMatcher() : parent::getMatcherInstance();
    }

    protected function getTransformerInstance(): AbstractTransformer
    {
        return ($this->galaxie_active) ? new GalaxieTransformer() : parent::getTransformerInstance();
    }

    protected function getConfiguration(array $additionnal_confs = []): Configuration
    {
        $additionnal_confs = array_merge(
            $additionnal_confs,
            [
                'plage_consult_start_hour' => CAppUI::conf('maidis plage_consult_start_hour'),
                'plage_consult_end_hour'   => CAppUI::conf('maidis plage_consult_end_hour'),
            ]
        );

        return parent::getConfiguration($additionnal_confs);
    }
}
