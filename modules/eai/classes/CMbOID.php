<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Interop\Hl7\CExchangeHL7v3;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Management of OID
 */
class CMbOID implements IShortNameAutoloadable {

    /** @var string */
    public const OX_ROOT_OID = "1.2.250.1.300";

    /** @var string */
    public const SYSTEM_CLASS_OID = '2';

  static $delimiter     = "1";
  static $class_mappage = [
      "CCompteRendu"        => "1",
      "CFile"               => "2",
      "CPatient"            => "3",
      "CMediusers"          => "4",
      "CGroups"             => "5",
      "CXDSSubmissionSet"   => "6",
      "CExchangeHL7v3"      => "7",
      "CSejour"             => "8",
      "CConsultation"       => "9",
      "CConsultAnesth"      => "10",
      "COperation"          => "11",
      "CIdSante400"         => "12",
  ];

    /**
   * Return the instance OID
   *
   * @param CInteropReceiver $receiver Receiver
   *
   * @return String
   */
  static function getOIDRoot($receiver = null) {
    if ($receiver) {
      $receiver->loadConfigValues();
    }

    if ($receiver && $receiver->_configs["use_receiver_oid"]) {
      return $receiver->OID;
    }

    return CAppUI::conf("mb_oid");
  }

  /**
   * Return the instance OID
   *
   * @param CMbObject        $class         Class
   * @param CInteropReceiver $receiver      Receiver
   * @param bool             $only_oid_root Only OID root
   *
   * @return string
   */
  static function getOIDOfInstance($class, $receiver = null, $only_oid_root = false) {
    $oid_root  = self::getOIDRoot($receiver);
    if ($only_oid_root) {
      return $oid_root;
    }

    $delimiter = self::$delimiter;
    $oid_group = self::getGroupId($class);

    return $oid_root.".".$delimiter.".".$oid_group;
  }

  /**
   * Return the group Id
   *
   * @param CMbObject $class Class
   *
   * @return string
   */
  static function getGroupId($class) {
    $object = null;
    $result = null;
    if ($class instanceof CFile || $class instanceof CCompteRendu) {
      /** @var CCompteRendu|CFile $class */
      $class = $object = $class->loadTargetObject();
    }
    if ($class instanceof CConsultAnesth) {
      /** @var CConsultAnesth $class */
      $class = $class->loadRefConsultation();
    }

    switch (get_class($class)) {
      case CMediusers::class:
        /** @var CMediusers $class */
        $result = $class->_group_id;
        break;
      case CSejour::class:
        /** @var CSejour $class */
        $result = $class->group_id;
        break;
      case COperation::class:
        /** @var COperation $class */
        $result = $class->loadRefSejour()->group_id;
        break;
      case CConsultAnesth::class:
        /** @var CConsultAnesth $class */
        $result = $class->loadRefConsultation()->loadRefGroup()->group_id;
        break;
      case CConsultation::class;
        /** @var CConsultation $class */
        $result = $class->loadRefGroup()->group_id;
        break;
      case CPatient::class:
        /** @var CPatient $class */
        $result = "0";
        break;
      case CGroups::class:
        /** @var CGroups $class */
        $result = $class->_id;
        break;
      case CXDSSubmissionSet::class:
        /** @var CXDSSubmissionSet $class */
        $result = $class->group->_id;
        break;
      case CExchangeHL7v3::class:
        /** @var CExchangeHL7v3 $class */
        $result = $class->group_id;
      default:
    }

    return $result;
  }

  /**
   * Return the class OID
   *
   * @param CMbObject        $class         Class
   * @param CInteropReceiver $receiver      Receiver
   * @param bool             $only_oid_root Only OID root
   *
   * @return string
   */
  static function getOIDFromClass($class, $receiver = null, $only_oid_root = false) {
    $oid_instance = self::getOIDOfInstance($class, $receiver, $only_oid_root);

    $delimiter    = self::$delimiter;
    $oid          = self::$class_mappage[$class->_class];

    return $oid_instance.".".$delimiter.".".$oid;
  }

    /**
     * Make an unique OID for class common in all instance
     *
     * @param string|CModelObject $class
     *
     * @return string
     * @throws CMbException
     */
    public static function getClassOID($class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $object = null;
        if (is_string($class)) {
            try {
                $object = CClassMap::getInstance()->getClassMap($class) ? new $class() : null;
            } catch (Exception $exception) {
                $object = null;
            }

            if (!$object) {
                $object = is_subclass_of($class, CModelObject::class) ? new $class() : null;
            }
        }

        if (!is_subclass_of($object, CModelObject::class)) {
            throw new CMbException('CMbOID-msg-invalid class given');
        }

        if (!array_key_exists($object->_class, self::$class_mappage)) {
            throw new CMbException('CMbOID-msg-not supported class');
        }

        return self::OX_ROOT_OID
            . "." . self::$delimiter
            . "." . self::SYSTEM_CLASS_OID
            . "." . self::$delimiter
            . "." . self::$class_mappage[$object->_class];
    }

    /**
     * Know if string is an OID of class
     *
     * @param string $oid
     *
     * @return bool
     */
    public static function isClassOID(string $oid): bool
    {
        return str_starts_with(
            $oid,
            self::OX_ROOT_OID
            . "." . self::$delimiter
            . "." . self::SYSTEM_CLASS_OID
            . "." . self::$delimiter
        );
    }

    /**
     * Get the OID common root
     *
     * @return void
     * @throws CMbException
     */
    public static function getOxOIDRoot(): string
    {
        if (!$oid_root = CAppUI::conf('mb_oid')) {
            throw new CMbException('CMbOid-msg-oid config missing');
        }

        $ox_oid_root = explode('.', $oid_root, 5);
        if (count($ox_oid_root) === 5) {
            unset($ox_oid_root[4]);
        }

        return implode('.', $ox_oid_root);
    }
}
