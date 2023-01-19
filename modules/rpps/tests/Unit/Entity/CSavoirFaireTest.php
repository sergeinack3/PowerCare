<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit\Entity;

use Exception;
use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Import\Rpps\Entity\CSavoirFaire;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CSavoirFaireTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->ds = CSQLDataSource::get(CExternalMedecinBulkImport::DSN, true);
        if (!$this->ds) {
            $this->markTestSkipped('Datasource RPPS is not available');
        }

        if (!$this->ds->hasTable('savoir_faire')) {
            // Create schema for tables to be available
            $import = new CExternalMedecinBulkImport();
            $import->createSchema();
            $this->markTestSkipped('savoir_faire table not existing');
        }
    }

    public function testSynchronizeEmpty(): void
    {
        $savoir_faire = new CSavoirFaire();
        $this->assertEquals(new CMedecin(), $savoir_faire->synchronize());
    }
}
