<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Mediboard\Jfse\Domain\Printing\PrintingCerfaConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingSlipConf;

class PrintingMapper extends AbstractMapper
{
    public function slipConfToArray(PrintingSlipConf $conf): array
    {
        $data = [
            "imprimerBordereau" => [
                "mode"        => $conf->getMode(),
                "modeDegrade" => (int)$conf->getDegrade(),
            ],
        ];

        $min = $conf->getDateMin();
        $max = $conf->getDateMax();

        self::addOptionalValue("dateDebut", ($min) ? $min->format("Ymd") : null, $data["imprimerBordereau"]);
        self::addOptionalValue("dateMax", ($max) ? $max->format("Ymd") : null, $data["imprimerBordereau"]);
        if ($conf->getBatch()) {
            $data["imprimerBordereau"]["lstLots"] = array_map(
                function ($batch) {
                    return ["idLot" => $batch];
                },
                $conf->getBatch()
            );
        }

        self::addOptionalValue("lstFichiers", $conf->getFiles(), $data["imprimerBordereau"]);

        return $data;
    }

    public function cerfaConfToArray(PrintingCerfaConf $printing_cerfa_conf): array
    {
        $data = [
            "duplicata"         => (int)$printing_cerfa_conf->getDuplicate()
        ];

        if ($printing_cerfa_conf->getUserSignature() !== null) {
            $data['utiliserSignature'] = (int)$printing_cerfa_conf->getUserSignature();
        }

        if ($printing_cerfa_conf->getUseBackground() !== null) {
            $data['utiliserFond'] = (int)$printing_cerfa_conf->getUseBackground();
        }

        self::addOptionalValue("noFacture", $printing_cerfa_conf->getInvoiceNumber(), $data);
        self::addOptionalValue("idFacture", $printing_cerfa_conf->getInvoiceId(), $data);

        return ['imprimerCerfa' => $data];
    }
}
