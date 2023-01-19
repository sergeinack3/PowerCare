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
use Ox\Import\Rpps\Entity\CDiplomeAutorisationExercice;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CDiplomeAutorisationExerciceTest extends OxUnitTestCase
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

        if (!$this->ds->hasTable('diplome_autorisation_exercice')) {
            // Create schema for tables to be available
            $import = new CExternalMedecinBulkImport();
            $import->createSchema();
            $this->markTestSkipped('diplome_autorisation_exercice table not existing');
        }
    }

    public function testSynchronizeEmpty(): void
    {
        $diplome = new CDiplomeAutorisationExercice();
        $this->assertEquals(new CMedecin(), $diplome->synchronize());
    }
}
