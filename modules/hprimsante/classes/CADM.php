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
 * Class CADM
 * Transfert de données d'admission
 */
class CADM extends CHPrimSante
{
    /** @var string[] Events */
    public static $evenements = [
        // L - Liaisons entre laboratoires
        "L" => "CHPrimSanteADML",
        // C - Liaisons entre laboratoires et établissements cliniques ou hospitaliers
        "C" => "CHPrimSanteADMC",
        // R - Liaisons entre cabinets de radiologie et établissements cliniques ou hospitaliers
        "R" => "CHPrimSanteADMR",
    ];

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->type = "ADM";

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * @inheritdoc
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $sous_type = $exchange->sous_type;
        //@todo voir pour la gestion
        $classname = "CHPrimSanteADM$sous_type";

        return new $classname();
    }
}

