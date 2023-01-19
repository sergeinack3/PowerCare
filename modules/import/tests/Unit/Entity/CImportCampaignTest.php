<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Entity;

use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObjectSpec;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\GenericImport\CImportFile;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class CImportCampaignTest extends OxUnitTestCase
{
    /** @var CImportCampaign */
    private $c_import_campaign;

    /** @var CMediusers */
    private $mediuser;

    public function setUp(): void
    {
        $this->c_import_campaign = new CImportCampaign();
        $this->mediuser          = $this->generateMediusers();
    }

    /*
     * Test if getSpec() return with good informations
     */
    public function testGetSpec(): void
    {
        $spec = $this->c_import_campaign->getSpec();

        $this->assertInstanceOf(CMbObjectSpec::class, $spec);
        $this->assertEquals('import_campaign', $spec->table);
        $this->assertEquals('import_campaign_id', $spec->key);
        $this->assertNotEmpty($spec->uniques);
    }

    /*
     * Test si store() AVEC un CImportCampaign->_id ne modifie pas le CImportCampaign
     */
    public function testStoreIfImportCampaignExist(): void
    {
        $medi_user = $this->mediuser;

        $c_import_campaign             = new CImportCampaign();
        $c_import_campaign->name       = 'toto';
        $c_import_campaign->_id        = 1;
        $c_import_campaign->creator_id = $medi_user->_id;

        $c_import_campaign->store();

        $this->assertEquals($medi_user->_id, $c_import_campaign->creator_id);
    }

    /*
     * Test si store() SANS un CImportCampaign->_id modifie le CImportCampaign
     */
    public function testStoreIfImportCampaignNotExist(): void
    {
        $c_import_campaign       = new CImportCampaign();
        $c_import_campaign->name = 'toto';

        $c_import_campaign->store();

        $this->assertEquals(CMediusers::get()->_id, $c_import_campaign->creator_id);
    }

    public function testStore(): void
    {
        $medi_user = $this->mediuser;

        $campaign             = new CImportCampaign();
        $campaign->creator_id = $medi_user->_id;

        $campaign->store();

        $campaign_after = new CImportCampaign();

        $campaign_after->load($campaign->_id);

        $this->assertMatchesRegularExpression("/\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}:\d{2}/", $campaign->creation_date);
    }

    // test d'exception si CImportCampaign n'as pas d'id
    public function testCloseWithoutId(): void
    {
        $c_import_campaign = new CImportCampaign();

        $this->expectExceptionMessage('CImportCampaign-error-Campaign does not exist');
        $c_import_campaign->close();
    }

    // test d'exception si CImportCampaign a été clôturée
    public function testCloseAlreadyClosed(): void
    {
        $c_import_campaign               = new CImportCampaign();
        $c_import_campaign->_id          = 1;
        $c_import_campaign->closing_date = "1900-12-12 20:20:20";

        $this->expectExceptionMessage('CImportCampaign-error-Campaign already closed');
        $c_import_campaign->close();
    }

    //test de la fermeture d'une campagne
    public function testClose(): void
    {
        $c_import_campaign                = new CImportCampaign();
        $c_import_campaign->name          = uniqid();
        $c_import_campaign->creation_date = CMbDT::dateTime("-1 DAY");

        $this->assertNull($c_import_campaign->store());
        $c_import_campaign->close();

        $this->assertMatchesRegularExpression(
            "/\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}:\d{2}/",
            $c_import_campaign->closing_date
        );
    }

    //test si il y a eu un problème lors du store
    public function testCloseWithErrorMsgWhenStore(): void
    {
        $medi_user = $this->mediuser;

        $import_campaign             = new CImportCampaign();
        $import_campaign->creator_id = $medi_user->_id;

        $import_campaign->store();
        $import_campaign->name = '';

        $this->expectException(CMbException::class);
        $import_campaign->close();
    }

    public function testGetMappedFiles(): void
    {
        $medi_user = $this->mediuser;

        $import_campaign             = new CImportCampaign();
        $import_campaign->name       = uniqid();
        $import_campaign->creator_id = $medi_user->_id;

        $this->storeOrFailed($import_campaign);

        $import_file                     = new CImportFile();
        $import_file->import_campaign_id = $import_campaign->_id;
        $import_file->file_name          = "patient.csv";

        $this->storeOrFailed($import_file);

        //test without type
        $this->assertEquals([], $import_campaign->getMappedFiles());

        $import_file                     = new CImportFile();
        $import_file->import_campaign_id = $import_campaign->_id;
        $import_file->file_name          = "patient.csv";
        $import_file->entity_type        = "patient";

        $this->storeOrFailed($import_file);

        //test with type
        $this->assertEquals(["patient"], $import_campaign->getMappedFiles());
    }

    private function generateMediusers(): CMediusers
    {
        return $this->getObjectFromFixturesReference(
            CMediusers::class,
            UsersFixtures::REF_USER_LOREM_IPSUM
        );
    }

    //TODO tester addImportedObject

    // test addImportedObject
    //    public function testAddImportedObject(): void
    //    {
    //        $external_object   = $this->createMock(EntityInterface::class);
    //        $c_import_campaign = new CImportCampaign();
    //
    //        $c_import_campaign->addImportedObject($external_object);
    //
    //
    //    }
}
