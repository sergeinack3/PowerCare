<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Interop\Webservices\Wsdl\FileWSDLRepository;
use Ox\Interop\Webservices\Wsdl\WSDLFactory;
use Ox\Interop\Webservices\Wsdl\WSDLRepository;
use Ox\Tests\OxUnitTestCase;

/**
 * Class WSDLRepositoryTest
 * Todo: Test delete and flush
 */
class WSDLRepositoryTest extends OxUnitTestCase
{
    /**
     * Remove the created files
     */
    static public function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $repository = new WSDLRepository(new FileWSDLRepository('soap_client'));
        $repository->flush();
    }

    /**
     * @return array
     */
    public function repositoryProvider()
    {
        return [
            ['login=user:pwd', null, 'system', 'about', 'CEAISoapHandler', 'CWSDLRPCEncoded'],
            ['login=user:pwd', null, 'system', 'about_2', 'CEAISoapHandler', 'CWSDLRPCEncoded'],
            [null, 'abcde', 'system', 'about', 'CEAISoapHandler', 'CWSDLRPCEncoded'],
            [null, 'abcde', 'system_2', 'about', 'CEAISoapHandler', 'CWSDLRPCEncoded'],
        ];
    }

    /**
     * @return array
     */
    public function repositoryFindProvider()
    {
        return [
            ['login=user:pwd', null, 'must_be', 'found', 'CEAISoapHandler', 'CWSDLRPCEncoded'],
            ['login=user:pwd', null, 'must_be', 'found', 'CEAISoapHandler', 'CWSDLRPCLiteral'],
        ];
    }

    /**
     * @param string|null $login
     * @param string|null $token
     * @param string      $module
     * @param string      $tab
     * @param string      $classname
     * @param string      $wsdl_mode
     *
     * @config       webservices wsdl_root_url http://127.0.0.1/mediboard
     *
     * @dataProvider repositoryProvider
     *
     * @throws CMbException
     */
    public function testWsdlNotFoundAndSaved(
        ?string $login,
        ?string $token,
        string $module,
        string $tab,
        string $classname,
        string $wsdl_mode
    ) {
        $repository = new WSDLRepository(new FileWSDLRepository('soap_client'));

        $wsdl = $repository->find($login, $token, $module, $tab, $classname, $wsdl_mode);

        // By altering arguments, except WSDL mode, WSDL should be unique
        $this->assertNull($wsdl);

        $wsdl = WSDLFactory::create($login, $token, $module, $tab, $classname, $wsdl_mode);

        $this->assertTrue($repository->save($wsdl));
    }

    /**
     * @param string|null $login
     * @param string|null $token
     * @param string      $module
     * @param string      $tab
     * @param string      $classname
     * @param string      $wsdl_mode
     *
     * @dataProvider repositoryFindProvider
     *
     * @throws CMbException
     */
    public function testWsdlFound(
        ?string $login,
        ?string $token,
        string $module,
        string $tab,
        string $classname,
        string $wsdl_mode
    ) {
        $repository = new WSDLRepository(new FileWSDLRepository("soap_client"));

        $wsdl = WSDLFactory::create($login, $token, $module, $tab, $classname, $wsdl_mode);
        $this->assertTrue($repository->save($wsdl));

        $wsdl = $repository->find($login, $token, $module, $tab, $classname, $wsdl_mode);

        // If WSDL found, we should have an instanceof CWSDL
        $this->assertNotNull($wsdl);
    }
}
