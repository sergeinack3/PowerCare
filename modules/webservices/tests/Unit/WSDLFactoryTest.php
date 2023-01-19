<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Tests\Unit;

use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use Ox\Interop\Webservices\Wsdl\WSDLFactory;
use Ox\Tests\OxUnitTestCase;

/**
 * Class WSDLFactoryTest
 * Todo: Test document validation
 */
class WSDLFactoryTest extends OxUnitTestCase {
  /**
   * @return array
   */
  public function createWsdlProvider() {
    $login     = 'login=user:pwd';
    $token     = 'abcde';
    $module    = 'system';
    $tab       = 'about';
    $handler   = 'CEAISoapHandler';
    $wsdl_mode = 'CWSDLRPCEncoded';

    return [
      'with_login'                 => [$login, null, $module, $tab, $handler, $wsdl_mode],
      'with_token'                 => [null, $token, $module, $tab, $handler, $wsdl_mode],
      'with_login_CEAISoapHandler' => [$login, null, $module, $tab, $handler, $wsdl_mode],
      'with_login_CWSDLRPCEncoded' => [$login, null, $module, $tab, $handler, $wsdl_mode],
      'with_login_CWSDLRPCLiteral' => [$login, null, $module, $tab, $handler, 'CWSDLRPCLiteral'],
      'with_token_CEAISoapHandler' => [null, $token, $module, $tab, $handler, $wsdl_mode],
      'with_token_CWSDLRPCEncoded' => [null, $token, $module, $tab, $handler, $wsdl_mode],
      'with_token_CWSDLRPCLiteral' => [null, $token, $module, $tab, $handler, 'CWSDLRPCLiteral'],
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
   * @dataProvider createWsdlProvider
   *
   * @throws CMbException
   */
  public function testCreateWsdl(?string $login, ?string $token, string $module, string $tab, string $classname, string $wsdl_mode) {
    $wsdl = WSDLFactory::create($login, $token, $module, $tab, $classname, $wsdl_mode);

    // Test if factory returns correct CWSDL instance
    $this->assertInstanceOf(CClassMap::getSN($wsdl_mode), $wsdl);

    // Test if unique name is set
    $this->assertNotNull($wsdl->getName());

    // Test if correct SOAP handler is set
    $this->assertInstanceOf(CClassMap::getSN($classname), $wsdl->_soap_handler);
  }

  /**
   * @return array
   */
  public function createWsdlFromStringProvider() {
    $xml = <<<EOF
<?xml version="1.0" encoding="iso-8859-1"?>
<!--WSDL Mediboard genere permettant de decrire le service web.-->
<!--Partie 1 : Definitions-->
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" name="MediboardWSDL" targetNamespace="http://soap.mediboard.org/wsdl/" xmlns:typens="http://soap.mediboard.org/wsdl/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
  <!--Partie 3 : Messages-->
  <message name="calculatorAuthRequest">
    <part name="operation" type="xsd:string"/>
    <part name="entier1" type="xsd:int"/>
    <part name="entier2" type="xsd:int"/>
  </message>
  <message name="calculatorAuthResponse">
    <part name="result" type="xsd:int"/>
  </message>
  <message name="eventRequest">
    <part name="message" type="xsd:string"/>
  </message>
  <message name="eventResponse">
    <part name="response" type="xsd:string"/>
  </message>
  <!--partie 4 : Port Type-->
  <portType name="MediboardPort">
    <!--partie 5 : Operation-->
    <operation name="calculatorAuth">
      <input message="typens:calculatorAuthRequest"/>
      <output message="typens:calculatorAuthResponse"/>
    </operation>
    <!--partie 5 : Operation-->
    <operation name="event">
      <input message="typens:eventRequest"/>
      <output message="typens:eventResponse"/>
    </operation>
  </portType>
  <!--partie 6 : Binding-->
  <binding name="MediboardBinding" type="typens:MediboardPort">
    <soap:binding xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="calculatorAuth">
      <soap:operation xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" soapAction="MediboardAction"/>
      <input name="calculatorAuthRequest">
        <soap:body xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" use="encoded" namespace="urn:MediboardWSDL" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output name="calculatorAuthResponse">
        <soap:body xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" use="encoded" namespace="urn:MediboardWSDL" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>
    <operation name="event">
      <soap:operation xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" soapAction="MediboardAction"/>
      <input name="eventRequest">
        <soap:body xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" use="encoded" namespace="urn:MediboardWSDL" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output name="eventResponse">
        <soap:body xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" use="encoded" namespace="urn:MediboardWSDL" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>
  </binding>
  <!--Partie 7 : Service-->
  <service name="MediboardService">
    <documentation>Documentation du WebService</documentation>
    <!--partie 8 : Port-->
    <port name="MediboardPort" binding="typens:MediboardBinding">
      <soap:address xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" location="https://localhost/mediboard/?login=user:pwd&amp;m=system&amp;a=about&amp;class=CEAISoapHandler&amp;wsdl_mode=CWSDLRPCEncoded&amp;suppressHeaders=1"/>
    </port>
  </service>
</definitions>
EOF;

    return [
      ['CWSDLRPCEncoded', 'CEAISoapHandler', 'toto', $xml],
      ['CWSDLRPCLiteral', 'CEAISoapHandler', 'titi', $xml],
    ];
  }

  /**
   * Valid XML content is provided. No Exception should be thrown.
   *
   * @param string $wsdl_mode
   * @param string $classname
   * @param string $name
   * @param string $xml
   *
   * @dataProvider createWsdlFromStringProvider
   *
   * @throws CMbException
   */
  public function testCreateWsdlFromValidString(string $wsdl_mode, string $classname, string $name, string $xml) {
    $wsdl = WSDLFactory::createFromString($wsdl_mode, $classname, $name, $xml);

    $this->assertInstanceOf(CClassMap::getSN($wsdl_mode), $wsdl);
    $this->assertEquals($name, $wsdl->getName());
    $this->assertInstanceOf(CClassMap::getSN($classname), $wsdl->_soap_handler);
  }

  /**
   * Invalid XML content is provided. A CMbException should occurs
   *
   * @throws CMbException
   */
  public function testCreateWsdlFromInvalidString() {
    $this->expectException(CMbException::class);

    WSDLFactory::createFromString('CWSDLRPCEncoded', 'CEAISoapHandler', 'toto', 'NOT XML');
  }
}
