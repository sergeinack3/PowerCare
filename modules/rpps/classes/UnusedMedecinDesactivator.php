<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use Exception;
use Ox\Core\CMbBackSpec;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Disable all unused medecins
 */
class UnusedMedecinDesactivator
{
    public const COLUMN_IS_USED = 'is_used';

    public const IGNORE_CLASSES = [
        'CExLink',
        'CMedecinExercicePlace',
        'CObservationResult',
    ];

    /** @var CSQLDataSource */
    private $ds;

    /** @var CMedecin */
    private $medecin;

    /** @var array */
    private $used_medecin_ids = [];

    private $count_disabled = 0;

    public function __construct()
    {
        $this->ds      = CSQLDataSource::get('std');
        $this->medecin = new CMedecin();
    }

    /**
     * @throws CMbException|Exception
     */
    public function disableMedecins(): int
    {
        $this->medecin->makeAllBackSpecs();
        foreach ($this->medecin->_backSpecs as $back_name => $spec) {
            $this->checkMedecinsToKeep($spec);
        }

        $this->disableUnusedMedecins();

        return $this->count_disabled;
    }

    /**
     * Check if the backref has to be checked.
     * Load medecin_ids that have are in used by other classes and tag them as "used".
     *
     * @throws Exception
     */
    private function checkMedecinsToKeep(CMbBackSpec $spec): void
    {
        if ($this->ignoreBackSpec($spec)) {
            return;
        }

        if ($medecin_ids = $this->loadIdsInUse($spec->class, $spec->field)) {
            $this->setMedecinsInUse($medecin_ids);
        }
    }

    private function ignoreBackSpec(CMbBackSpec $spec): bool
    {
        return $spec->_unlink || $spec->_cascade || in_array($spec->class, self::IGNORE_CLASSES);
    }

    /**
     * @throws Exception
     */
    protected function loadIdsInUse(string $short_class_name, string $field_name): array
    {
        /** @var CStoredObject $instance */
        $instance = new $short_class_name();
        $field_spec = $instance->_specs[$field_name];

        // Check if a table is defined, the spec is a CRefSpec and references a CMedecin
        if (
            !$instance->_spec->table
            || !$field_spec instanceof CRefSpec
            || ($field_spec->class !== 'CMedecin' && !isset($field_spec->meta))
        ) {
            return [];
        }

        $ds = $instance->getDS();

        // Table of an object or a column can be missing depending of the order of setups
        if (!$ds->hasTable($instance->_spec->table) || !$ds->hasField($instance->_spec->table, $field_name)) {
            return [];
        }

        $query         = new CRequest();
        $query->addSelect($field_name);
        $query->addTable($instance->_spec->table);

        /** @var CRefSpec $field_spec */
        if ($field_spec->meta) {
            $query->addWhere([$instance->_specs[$field_spec->meta]->fieldName => "= 'CMedecin'"]);
        }

        return $ds->loadColumn($query->makeSelect()) ?: [];
    }

    private function setMedecinsInUse(array $medecin_ids): void
    {
        foreach ($medecin_ids as $med_id) {
            if ($med_id !== null && $med_id !== '') {
                $this->used_medecin_ids[$med_id] = true;
            }
        }
    }

    /**
     * @throws CMbException|Exception
     */
    private function disableUnusedMedecins(): void
    {
        if (!$this->used_medecin_ids) {
            return;
        }

        $where = [
            "`actif` = '1'",
            '`group_id` IS NULL',
            '`function_id` IS NULL',
            '`medecin_id` NOT IN (' . implode(',', array_keys($this->used_medecin_ids)) . ')',
        ];

        $query = "UPDATE `medecin` SET `actif` = '0' WHERE " . implode(' AND ', $where);

        if (!$this->executeQuery($query)) {
            throw new CMbException($this->ds->error());
        }

        $this->count_disabled = $this->getAffectedRow();
    }

    /**
     * @throws Exception
     */
    protected function executeQuery(string $query): bool
    {
        return (bool)$this->ds->exec($query);
    }

    protected function getAffectedRow(): int
    {
        return $this->ds->affectedRows() ?? 0;
    }
}
