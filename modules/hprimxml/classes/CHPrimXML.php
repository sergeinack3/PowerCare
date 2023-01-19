<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Class CHPrimXML
 */
class CHPrimXML extends CInteropNorm
{
    /** @var string[] Handlers */
    public static $object_handlers = [
        "CSipObjectHandler"     => "CSipHprimXMLObjectHandler",
        "CSmpObjectHandler"     => "CSmpHprimXMLObjectHandler",
        "CSaObjectHandler"      => "CSaHprimXMLObjectHandler",
        "CSaEventObjectHandler" => "CSaEventHprimXMLObjectHandler",
    ];

    /** @var string[] Version */
    public static $versions = [
        "patients"            => [
            "evt_patients" => ["1.05", "1.052", "1.053", "1.054", "1.06", "1.07"],
        ],
        "serveurActivitePMSI" => [
            "evt_serveuractes"        => ["1.01", "1.04", "1.05", "1.06", "1.07", "2.00"],
            "evt_pmsi"                => ["1.01", "1.04", "1.05", "1.06", "1.07", "2.00"],
            "evt_serveuretatspatient" => ["1.04", "1.05", "1.06", "1.07", "2.00"],
            "evt_frais_divers"        => ["1.05", "1.06", "1.07", "2.00"],
            "evt_serveurintervention" => ["1.06", "1.07", "1.072", "2.00"],
        ],
        "stock"               => [
            "evt_mvtStock" => ["1.01", "1.02"],
        ],
    ];

    /** @var array Elements */
    public static $documentElements = [];

    /**
     * Get object handlers
     *
     * @return array
     */
    public static function getObjectHandlers(): ?array
    {
        return self::$object_handlers;
    }

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->name = "CHPrimXML";

        parent::__construct();
    }

    /**
     * Récupération des évènements disponibles
     *
     * @return array
     */
    public function getDocumentElements(): ?array
    {
        return self::$documentElements;
    }

    /**
     * Get event
     *
     * @param string $messagePatient Message
     *
     * @return CHPrimXMLEvenementsPatients|void
     */
    public static function getHPrimXMLEvenements($messagePatient)
    {
    }

    /**
     * Get object tag
     *
     * @param string $group_id Group
     *
     * @return string|null
     */
    public static function getObjectTag($group_id = null): ?string
    {
        // Recherche de l'établissement
        $group = CGroups::get($group_id);
        if (!$group_id) {
            $group_id = $group->_id;
        }

        // Todo: Take care of LSB here
        $cache = new Cache('CHPrimXML.getObjectTag', [$group_id], Cache::INNER);

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
        return CAppUI::conf("hprimxml tag_default");
    }
}
