<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Hprim sante utility class
 */
class CHPrimSante extends CInteropNorm
{
    /** @var array */
    public static $object_handlers = [
        "CSipObjectHandler" => "CADMDelegatedHandler",
    ];

    /** @var array */
    public static $versions = [
        "2.1",
        "2.2",
        "2.3",
        "2.4",
        "2.5",
    ];

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->name = "CHPrimSante";

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getVersions(): ?array
    {
        return self::$versions;
    }

    /**
     * @inheritdoc
     */
    public static function getObjectHandlers(): ?array
    {
        return self::$object_handlers;
    }

    /**
     * @inheritdoc
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        switch ($exchange->type) {
            case "ADM":
                return CADM::getEvent($exchange);

            case "ORU":
                return CORU::getEvent($exchange);

            case "REG":
                return CREG::getEvent($exchange);

            default:
                throw new CMbException("CIHE_event-unknown");
        }
    }

    /**
     * Get tag
     *
     * @param int $group_id group id
     *
     * @return mixed|null
     */
    public static function getTag(?int $group_id = null): ?string
    {
        // Pas de tag Identifiant
        if (null == $tag = CAppUI::conf("hprimsante tag")) {
            return null;
        }

        // Permettre des ID en fonction de l'établissement
        $group = CGroups::loadCurrent();
        if (!$group_id) {
            $group_id = $group->_id;
        }

        return str_replace('$g', $group_id, $tag);
    }

    /**
     * Format hprim biologie
     *
     * @param String $string message
     *
     * @return bool|mixed|string
     */
    public static function formatHPRIMBiologie(string $string)
    {
        if (substr($string, 0, 2) === "\xFF\xFE") {
            // UTF-16 with BOM
            $string = iconv("UTF-16", "iso-8859-1//TRANSLIT//IGNORE", $string);
        } elseif (strpos($string, "\x8E") !== false) {
            // MacRoman
            $string = iconv("macintosh", "iso-8859-1//TRANSLIT//IGNORE", $string);
        }

        $lines = preg_split("/(\r\n|\n)/", $string, 13);

        $lines[10] = preg_split("/\s/", $lines[10], 2); // Expediteur
        $lines[11] = preg_split("/\s/", $lines[11], 2); // Destinataire

        $all_text = $lines[12];
        unset($lines[12]);

        $lab_header = "****LAB****";
        $fin_header = "****FIN****";

        $lab_pos = strpos($all_text, $lab_header);
        $fin_pos = strpos($all_text, $fin_header);

        $text  = substr($all_text, 0, $lab_pos);
        $hprim = trim(substr($all_text, $lab_pos + strlen($lab_header), $fin_pos - $lab_pos - strlen($fin_header)));
        $hprim = preg_split("/(\r\n|\n)/", $hprim);

        $pattern = "/^RES\|" .      // 01
            "(?P<label>[^\|]*)\|" .   // 02
            "(?P<code>[^\|]*)\|" .    // 03
            "(?P<type>[^\|]*)\|" .    // 04
            "(?P<value>[^\|]*)\|" .   // 05
            "(?P<unit>[^\|]*)\|" .    // 06
            "(?P<min>[^\|]*)\|" .     // 07
            "(?P<max>[^\|]*)\|" .     // 08
            "(?P<anormal>[^\|]*)\|" . // 09
            "(?P<status>[^\|]*)\|" .  // 10
            "(?P<value2>[^\|]*)\|" .  // 11
            "(?P<unit2>[^\|]*)\|" .   // 12
            "(?P<min2>[^\|]*)\|" .    // 13
            "(?P<max2>[^\|]*)\|" .    // 14
            "/";

        $anormalites = [
            "L"  => "inférieur à la normale",
            "H"  => "supérieur à la normale",
            "LL" => "inférieur à la valeur panique basse",
            "HH" => "supérieur à la valeur panique haute",
            "N"  => "normal",
        ];

        $classes = [
            "L"  => "warning",
            "H"  => "warning",
            "LL" => "error",
            "HH" => "error",
            "N"  => "",
        ];

        $results = [];
        foreach ($hprim as $_hprim) {
            $matches = [];
            if (preg_match($pattern, $_hprim, $matches)) {
                $matches["anormal_text"]  = CValue::read($anormalites, $matches["anormal"]);
                $matches["anormal_class"] = CValue::read($classes, $matches["anormal"]);

                $results[] = $matches;
            }
        }

        $template = new CSmartyDP("modules/hprim21");
        $template->assign("header", $lines);
        $template->assign("text", $text);
        $template->assign("results", $results);

        return $template->fetch("inc_hprim_biologie_results.tpl");
    }
}
