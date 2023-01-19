<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit;

use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class CExternalMedecinBulkImportTest extends OxUnitTestCase
{
    private const TABLE_DIPLOME = 'diplome_autorisation_exercice';
    private const TABLE_SAVOIR_FAIRE = 'savoir_faire';
    private const TABLE_PERSONNE = 'personne_exercice';
    private const TABLE_MSSANTE = 'mssante_info';

    private const TABLE_NAMES = [
        self::TABLE_DIPLOME, self::TABLE_SAVOIR_FAIRE, self::TABLE_PERSONNE, self::TABLE_MSSANTE
    ];

    /** @var CSQLDataSource */
    private $ds;

    public function testImportTablesOk()
    {
        $this->ds = CSQLDataSource::get(CExternalMedecinBulkImport::DSN);
        if (!$this->ds) {
            $this->markTestSkipped('Datasource RPPS is not available');
        }

        $this->removeTables();

        foreach (self::TABLE_NAMES as $table_name) {
            $this->assertFalse($this->ds->hasTable($table_name, false));
        }

        $import = new CExternalMedecinBulkImport();
        $import->createSchema();

        foreach (self::TABLE_NAMES as $table_name) {
            $this->assertTrue($this->ds->hasTable($table_name, false));
        }
    }

    public function testImportTablesNoDs()
    {
        $import = new CExternalMedecinBulkImport(false);
        $this->assertFalse($import->createSchema());
    }

    public function testBulkImportNoDs(): void
    {
        $import = new CExternalMedecinBulkImport(false);
        $this->assertEquals([], $import->bulkImport(false));
    }

    private function removeTables()
    {
        $this->ds->exec(
            'DROP TABLE IF EXISTS `diplome_autorisation_exercice`, `savoir_faire`, `personne_exercice`, `mssante_info`'
        );
    }
}
