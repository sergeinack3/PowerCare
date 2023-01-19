<?php

/**
 * @package Mediboard\Ccam\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Tests\Unit;

use Exception;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\Ccam\Tests\Fixtures\CDevisCodageFixtures;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Tests\OxUnitTestCase;

class CDevisCodageTest extends OxUnitTestCase
{
    /**
     * Create category file object
     *
     * @throws Exception
     */
    protected static function createCategoryFile(): CFilesCategory
    {
        $file_category        = new CFilesCategory();
        $file_category->class = "CDevisCodage";
        $file_category->loadMatchingObjectEsc();

        if (!$file_category->_id) {
            $file_category->nom = "devis codage";

            if ($msg = $file_category->store()) {
                self::fail($msg);
            }
        }

        return $file_category;
    }

    /**
     * Test to generate the CFile object from CDevisCodage object
     *
     * @throws Exception
     */
    public function testGenerateFileFromDevisCodage(): void
    {
        $this->markTestSkipped(
            'Uncaught Error: Using $this when not in object context in print_devis_codage_to_pdf.tpl'
        );

        self::createCategoryFile();

        /** @var CDevisCodage $devis */
        $devis = $this->getObjectFromFixturesReference(CDevisCodage::class, CDevisCodageFixtures::TAG_DEVIS_CODAGE);

        $devis->_generate_pdf = 1;

        if ($msg = $devis->store()) {
            self::fail($msg);
        }

        $devis_file               = new CFile();
        $devis_file->object_class = $devis->codable_class;
        $devis_file->object_id    = $devis->codable_id;
        $devis_file->loadMatchingObject();

        $this->assertNotNull($devis_file->_id);
    }
}
