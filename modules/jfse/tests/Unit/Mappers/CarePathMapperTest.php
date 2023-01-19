<?php

/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathDoctor;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Exceptions\CarePath\CarePathMappingException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use ReflectionClass;

class CarePathMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider storeRequestProvider
     */
    public function testMakeArrayFromEntity(CarePath $care_path, array $expected): void
    {
        $actual = CarePathMapper::getSaveRequestFromEntity($care_path);

        $this->assertEquals($expected, $actual);
    }


    public function storeRequestProvider(): array
    {
        $reflection = new ReflectionClass(CarePathDoctor::class);
        $doctor     = $reflection->newInstanceWithoutConstructor();
        $prop       = $reflection->getProperty('last_name');
        $prop->setAccessible(true);
        $prop->setValue($doctor, 'Doe');
        $prop = $reflection->getProperty('first_name');
        $prop->setAccessible(true);
        $prop->setValue($doctor, 'John');
        $prop = $reflection->getProperty('invoicing_id');
        $prop->setAccessible(true);
        $prop->setValue($doctor, '123456789');

        $entity_U   = CarePath::hydrate(['invoice_id' => 1, 'indicator' => CarePathEnum::EMERGENCY()]);
        $expected_U = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'U']];

        $entity_T   = CarePath::hydrate(['invoice_id' => 1, 'indicator' => CarePathEnum::REFERRING_PHYSICIAN()]);
        $expected_T = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'T']];

        $entity_N   = CarePath::hydrate(['invoice_id' => 1, 'indicator' => CarePathEnum::NEW_RP()]);
        $expected_N = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'N']];

        $entity_R   = CarePath::hydrate(['invoice_id' => 1, 'indicator' => CarePathEnum::RP_SUBSTITUTE()]);
        $expected_R = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'R', "declaration" => '1']];

        $entity_O   = CarePath::hydrate(
            ['invoice_id' => 1, 'indicator' => CarePathEnum::ORIENTED_BY_RP(), 'doctor' => $doctor]
        );
        $expected_O = [
            "idFacture"     => 1,
            "parcoursSoins" => [
                "indicateur" => 'O',
                'medecin'    => [
                    'nom'              => 'Doe',
                    'prenom'           => 'John',
                    'noIdentification' => '123456789',
                ],
            ],
        ];

        $entity_M   = CarePath::hydrate(
            [
                'invoice_id'  => 1,
                'indicator'   => CarePathEnum::ORIENTED_BY_NRP(),
                'declaration' => true,
                'doctor'      => $doctor,
            ]
        );
        $expected_M = [
            "idFacture"     => 1,
            "parcoursSoins" => [
                "indicateur"  => 'M',
                "declaration" => '1',
                'medecin'     => [
                    'nom'              => 'Doe',
                    'prenom'           => 'John',
                    'noIdentification' => '123456789',
                ],
            ],
        ];

        $entity_J   = CarePath::hydrate(
            [
                'invoice_id'   => 1,
                'indicator'    => CarePathEnum::RECENTLY_INSTALLED_RP(),
                'declaration'  => false,
                'install_date' => new DateTimeImmutable(),
            ]
        );
        $expected_J = [
            "idFacture"     => 1,
            "parcoursSoins" => [
                "indicateur"       => 'J',
                "declaration"      => '2',
                'dateInstallation' => (new DateTimeImmutable())->format('Ymd'),
            ],
        ];

        $entity_Jbis   = CarePath::hydrate(
            [
                'invoice_id'   => 1,
                'indicator'    => CarePathEnum::RECENTLY_INSTALLED_RP(),
                'declaration'  => true,
                'install_date' => new DateTimeImmutable(),
            ]
        );
        $expected_Jbis = [
            "idFacture"     => 1,
            "parcoursSoins" => [
                "indicateur"       => 'J',
                "declaration"      => '1',
                'dateInstallation' => (new DateTimeImmutable())->format('Ymd'),
            ],
        ];

        $entity_B   = CarePath::hydrate(
            [
                'invoice_id'                => 1,
                'indicator'                 => CarePathEnum::POOR_MEDICALIZED_ZONE(),
                'declaration'               => false,
                'poor_md_zone_install_date' => new DateTimeImmutable(),
            ]
        );
        $expected_B = [
            "idFacture"     => 1,
            "parcoursSoins" => [
                "indicateur"                          => 'B',
                "declaration"                         => '2',
                'dateInstallationZoneSousMedicalisee' => (new DateTimeImmutable())->format(
                    'Ymd'
                ),
            ],
        ];

        $entity_D   = CarePath::hydrate(
            ['invoice_id' => 1, 'indicator' => CarePathEnum::SPECIFIC_DIRECT_ACCESS(), 'declaration' => false]
        );
        $expected_D = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'D', "declaration" => '2']];

        $entity_H   = CarePath::hydrate(
            ['invoice_id' => 1, 'indicator' => CarePathEnum::OUT_OF_RESIDENCY(), 'declaration' => false]
        );
        $expected_H = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'H', "declaration" => '2']];

        $entity_S1   = CarePath::hydrate(
            ['invoice_id' => 1, 'indicator' => CarePathEnum::NOT_SPECIFIC_ACCESS(), "declaration" => true]
        );
        $expected_S1 = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'S1', "declaration" => '1']];

        $entity_S2   = CarePath::hydrate(
            ['invoice_id' => 1, 'indicator' => CarePathEnum::NON_COMPLIANCE_CARE_PATH(), 'declaration' => false]
        );
        $expected_S2 = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'S2', "declaration" => '2']];

        $entity_S2bis   = CarePath::hydrate(
            ['invoice_id' => 1, 'indicator' => CarePathEnum::NON_COMPLIANCE_CARE_PATH()]
        );
        $expected_S2bis = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'S2']];

        return [
            [$entity_U, $expected_U],
            [$entity_T, $expected_T],
            [$entity_N, $expected_N],
            [$entity_R, $expected_R],
            [$entity_O, $expected_O],
            [$entity_M, $expected_M],
            [$entity_J, $expected_J],
            [$entity_Jbis, $expected_Jbis],
            [$entity_B, $expected_B],
            [$entity_D, $expected_D],
            [$entity_H, $expected_H],
            [$entity_S1, $expected_S1],
            [$entity_S2, $expected_S2],
            [$entity_S2bis, $expected_S2bis],
        ];
    }

    public function testMakeArrayFromEntityExpectExceptionWhenMissingDeclaration(): void
    {
        $entity   = CarePath::hydrate(['invoice_id' => 1, 'indicator' => CarePathEnum::SPECIFIC_DIRECT_ACCESS()]);
        $expected = ["idFacture" => 1, "parcoursSoins" => ["indicateur" => 'D', "declaration" => true]];

        $this->expectException(CarePathMappingException::class);
        $this->assertEquals($expected, CarePathMapper::getSaveRequestFromEntity($entity));
    }
}
