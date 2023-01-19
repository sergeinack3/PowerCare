<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Interop\Eai\CExchangeDataFormat;

/**
 * Class CORU
 * Transfert de résultats d'analyses,
 */
class CORU extends CHPrimSante
{
    /** @var string[] Events */
    public static $evenements = [
        // L - Liaisons entre laboratoires
        "L" => "CHPrimSanteORUL",
        // C - Liaisons entre laboratoires et établissements cliniques ou hospitaliers
        "C" => "CHPrimSanteORUC",
        // R - Liaisons entre cabinets de radiologie et établissements cliniques ou hospitaliers
        "R" => "CHPrimSanteORUR",
    ];

    /**
     * construct
     */
    public function __construct()
    {
        $this->type = "ORU";

        parent::__construct();
    }

    /**
     * Retrieve events list of data format
     *
     * @return array Events list
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object|null An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $code = $exchange->code;
        //@todo voir pour la gestion
        $classname = "CHPrimSanteORU$code";

        return new $classname();
    }
}

