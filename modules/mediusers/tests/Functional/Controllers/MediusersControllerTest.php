<?php
/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Mediusers\Tests\Functional\Controllers;

use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediusersControllerTest extends OxWebTestCase
{
    public function testListMediusers()
    {
        $client = static::createClient();
        $client->request('GET', '/api/mediuser/mediusers');

        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals($collection->getFirstItem()->getType(), 'mediuser');
    }

    public function testShowMediuser()
    {
        $mediuser = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $client   = static::createClient();
        $client->request('GET', '/api/mediuser/mediusers/' . $mediuser->_id);

        $this->assertResponseIsSuccessful();

        $item = $this->getJsonApiItem($client);

        $this->assertEquals($item->getType(), 'mediuser');
        $this->assertEquals($item->getId(), $mediuser->_id);
        $this->assertEquals($item->getAttribute('_user_first_name'), $mediuser->_user_first_name);
        $this->assertEquals($item->getAttribute('_user_last_name'), $mediuser->_user_last_name);
    }

    public function testShowMediuserFunctions()
    {
        $mediuser = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $client   = static::createClient();
        $client->request('GET', '/api/mediuser/mediusers/' . $mediuser->_id . '/functions');


        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);

        $this->assertGreaterThanOrEqual(1, $collection->count());
    }

    public function testImportMediuserNoFile()
    {
        $client = static::createClient();
        $client->request('POST', '/api/mediuser/mediusers', [
            'dry_run'            => 0,
            'update_found_users' => 1,
        ]);

        $this->assertEquals($client->getResponse()->getStatusCode(), 500);
        $error = $this->getJsonApiError($client);

        $this->assertStringStartsWith('Un fichier doit être uploadé via le nom "import_file"', $error->getMessage());
    }

    public function testImportMediuserSucces()
    {
        $tmp_file = tempnam("./tmp", "import_file");
        if (file_exists($tmp_file)) {
            (new Filesystem())->remove($tmp_file);
        }
        $content = "nom;prenom;username;password;type;fonction;profil\n";
        $content .= "import_file_lorem;import_file_ipsum;import_file_lipsum;azerty123;1;OpenXtrem;SI";

        (new Filesystem())->dumpFile($tmp_file, $content);

        $client = static::createClient();

        $upload_file = new UploadedFile($tmp_file, 'import_file');

        $client->request(
            'POST',
            '/api/mediuser/mediusers',
            [
                'dry_run'            => true,
                'update_found_users' => 1,
            ],
            [
                'import_file' => $upload_file,
            ]
        );


        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);
        $this->assertEquals($item->getAttribute('created')[0], 'Utilisateur créé (x 1)');
    }
}
