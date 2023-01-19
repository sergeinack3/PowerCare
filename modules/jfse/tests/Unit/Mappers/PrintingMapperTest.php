<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingCerfaConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingSlipConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintSlipModeEnum;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class PrintingMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider confToArrayProvider
     */
    public function testConfToArray(PrintingSlipConf $conf, array $expected): void
    {
        $this->assertEquals($expected, (new PrintingMapper())->slipConfToArray($conf));
    }

    public function confToArrayProvider(): array
    {
        $conf1 = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_PRINT()->getValue(), 0);
        $conf1->setDateMin(new DateTimeImmutable("2020-11-01"));
        $conf1->setDateMax(new DateTimeImmutable("2020-11-12"));
        $conf1->setBatch([1, 2, 3]);
        $conf1->setFiles([1, 2]);

        $conf2 = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_PRINT()->getValue(), 0);
        $conf2->setBatch([1, 2, 3]);

        $conf3 = new PrintingSlipConf(PrintSlipModeEnum::MODE_PRINT_DATE_BOUNDS()->getValue(), 0);
        $conf3->setDateMin(new DateTimeImmutable("2020-11-01"));
        $conf3->setDateMax(new DateTimeImmutable("2020-11-12"));

        $conf4 = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_OR_SEVERAL_FILES()->getValue(), 0);
        $conf4->setFiles([1, 2]);

        return [
            [
                $conf1,
                [
                    "imprimerBordereau" => [
                        "mode"        => PrintSlipModeEnum::MODE_ONE_PRINT()->getValue(),
                        "modeDegrade" => 0,
                        "dateDebut"   => "20201101",
                        "dateMax"     => "20201112",
                        "lstLots"     => [["idLot" => 1], ["idLot" => 2], ["idLot" => 3]],
                        "lstFichiers" => [1, 2],
                    ],
                ],
            ],
            [
                $conf2,
                [
                    "imprimerBordereau" => [
                        "mode"        => PrintSlipModeEnum::MODE_ONE_PRINT()->getValue(),
                        "modeDegrade" => 0,
                        "lstLots"     => [["idLot" => 1], ["idLot" => 2], ["idLot" => 3]],
                    ],
                ],
            ],
            [
                $conf3,
                [
                    "imprimerBordereau" => [
                        "mode"        => PrintSlipModeEnum::MODE_PRINT_DATE_BOUNDS()->getValue(),
                        "modeDegrade" => 0,
                        "dateDebut"   => "20201101",
                        "dateMax"     => "20201112",
                    ],
                ],
            ],
            [
                $conf4,
                [
                    "imprimerBordereau" => [
                        "mode"        => PrintSlipModeEnum::MODE_ONE_OR_SEVERAL_FILES()->getValue(),
                        "modeDegrade" => 0,
                        "lstFichiers" => [1, 2],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider cerfaConfProvider
     */
    public function testCerfaConfToArray(PrintingCerfaConf $print_cerfa_conf, array $expected): void
    {
        $this->assertEquals($expected, (new PrintingMapper())->cerfaConfToArray($print_cerfa_conf));
    }

    public function cerfaConfProvider(): array
    {
        $print_cerfa_conf1 = new PrintingCerfaConf(true, true, true);
        $print_cerfa_conf1->setInvoiceNumber(123456789);

        $print_cerfa_conf2 = new PrintingCerfaConf(false, false, true);
        $print_cerfa_conf2->setInvoiceNumber(123456789);

        $print_cerfa_conf3 = new PrintingCerfaConf(true, false, false);
        $print_cerfa_conf3->setInvoiceNumber(123456789);

        $print_cerfa_conf4 = new PrintingCerfaConf(true, true, true);
        $print_cerfa_conf4->setInvoiceId(987654321);

        return [
            [
                $print_cerfa_conf1,
                [
                    'imprimerCerfa' => [
                        "noFacture"         => 123456789,
                        "duplicata"         => 1,
                        "utiliserSignature" => 1,
                        "utiliserFond"      => 1,
                    ],
                ],
            ],
            [
                $print_cerfa_conf2,
                [
                    'imprimerCerfa' => [
                        "noFacture"         => 123456789,
                        "duplicata"         => 0,
                        "utiliserSignature" => 0,
                        "utiliserFond"      => 1,
                    ],
                ],
            ],
            [
                $print_cerfa_conf3,
                [
                    'imprimerCerfa' => [
                        "noFacture"         => 123456789,
                        "duplicata"         => 1,
                        "utiliserSignature" => 0,
                        "utiliserFond"      => 0,
                    ],
                ],
            ],
            [
                $print_cerfa_conf4,
                [
                    'imprimerCerfa' => [
                        "idFacture"         => 987654321,
                        "duplicata"         => 1,
                        "utiliserSignature" => 1,
                        "utiliserFond"      => 1,
                    ],
                ],
            ],
        ];
    }
}
