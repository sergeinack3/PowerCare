<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Hl7\Events\CHL7ADT;
use Ox\Interop\Hl7\Events\CHL7MDM;
use Ox\Interop\Hl7\Events\CHL7MFN;
use Ox\Interop\Hl7\Events\CHL7ORU;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Class CHL7
 * Tools
 */
class CHL7 extends CInteropNorm
{
    /** @var array versions */
    public static $versions = [];

    /**
     * @var array
     */
    public static $object_handlers = [
        'CSipObjectHandler'   => 'CADTDelegatedHandler',
        'CFilesObjectHandler' => [
            'CORUDelegatedHandler',
            'CMDMDelegatedHandler',
        ],
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->name = "CHL7";

        parent::__construct();
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        switch ($exchange->type) {
            case "CHL7ORU":
                return CHL7ORU::getEvent($exchange);

            case "CHL7MFN":
                return CHL7MFN::getEvent($exchange);

            case "CHL7ADT":
                return CHL7ADT::getEvent($exchange);

            case "CHL7MDM":
                return CHL7MDM::getEvent($exchange);

            default:
                $code    = $exchange->code;
                $version = $exchange->version;

                foreach (CHL7::$versions as $_version => $_sub_versions) {
                    if (in_array($version, $_sub_versions)) {
                        $classname = "CHL7{$_version}Event{$exchange->type}$code";

                        return new $classname();
                    }
                }
        }

        return null;
    }

    /**
     * Get object tag
     *
     * @param string $group_id Group
     *
     * @return string|null
     */
    public static function getObjectTag(?int $group_id = null): ?string
    {
        // Recherche de l'établissement
        $group = CGroups::get($group_id);
        if (!$group_id) {
            $group_id = $group->_id;
        }

        // Todo: Take care of LSB here
        $cache = new Cache('CHL7.getObjectTag', [$group_id], Cache::INNER);

        if ($cache->exists()) {
            return $cache->get();
        }

        $tag = self::getDynamicTag();

        return $cache->put(str_replace('$g', $group_id, $tag));
    }

    /**
     * Get object dynamic tag
     *
     * @return string
     */
    public static function getDynamicTag(): ?string
    {
        return CAppUI::conf("hl7 tag_default");
    }

    /**
     * Retrieve handlers list
     *
     * @return array Handlers list
     */
    public static function getObjectHandlers(): ?array
    {
        return self::$object_handlers;
    }
}

CHL7::$versions = [
    "v2" => CHL7v2::$versions,
];
