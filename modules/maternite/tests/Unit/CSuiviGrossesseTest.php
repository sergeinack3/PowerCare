<?php

namespace Ox\Mediboard\Maternite\Tests\Unit;

use Ox\Mediboard\Maternite\CSuiviGrossesse;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CSuiviGrossesseTest
 * @package Ox\Mediboard\Maternite\Tests\Unit
 */
class CSuiviGrossesseTest extends OxUnitTestCase
{

    /**
     * @param CSuiviGrossesse $suivi
     * @param array           $expected
     *
     * @dataProvider sortAttributesProvider
     * @throws \Exception
     */
    public function testSortAttributesByCategory(CSuiviGrossesse $suivi, array $expected): void
    {
        $actual = $suivi->sortAttributesByCategory();
        $this->assertEquals($expected, $actual);
    }

    public function sortAttributesProvider(): array
    {
        $full_object                           = new CSuiviGrossesse();
        $full_object->type_suivi               = "urg";
        $full_object->evenements_anterieurs    = "exenements anterieurs";
        $full_object->metrorragies             = "0";
        $full_object->leucorrhees              = "1";
        $full_object->contractions_anormales   = "0";
        $full_object->mouvements_foetaux       = "1";
        $full_object->troubles_digestifs       = "0";
        $full_object->troubles_urinaires       = "1";
        $full_object->autres_anomalies         = "efzfezz";
        $full_object->hypertension             = "0";
        $full_object->mouvements_actifs        = "percu";
        $full_object->auscultation_cardio_pulm = "anomalie";
        $full_object->examen_seins             = "normal";
        $full_object->circulation_veineuse     = "insmod";
        $full_object->oedeme_membres_inf       = "1";
        $full_object->rques_examen_general     = "zqfdgh njffgs  fdsvds";
        $full_object->bruit_du_coeur           = "percu";
        $full_object->col_normal               = "n";
        $full_object->longueur_col             = "long";
        $full_object->position_col             = "inter";
        $full_object->dilatation_col           = "ferme";
        $full_object->dilatation_col_num       = "2";
        $full_object->consistance_col          = "moy";
        $full_object->col_commentaire          = "azertyui";
        $full_object->presentation_position    = "tra";
        $full_object->presentation_etat        = "fix";
        $full_object->segment_inferieur        = "namp";
        $full_object->membranes                = "int";
        $full_object->bassin                   = "normal";
        $full_object->examen_genital           = "normal";
        $full_object->rques_exam_gyneco_obst   = "Lorem ipsum dolor sit amet";
        $full_object->hauteur_uterine          = "2";
        $full_object->frottis                  = "fait";
        $full_object->echographie              = "nfait";
        $full_object->prelevement_bacterio     = "nfait";
        $full_object->autre_exam_comp          = "adfeger htr jhytjtyedjxgfdshtfdhs fgs";
        $full_object->glycosurie               = "positif";
        $full_object->leucocyturie             = "negatif";
        $full_object->albuminurie              = "positif";
        $full_object->nitrites                 = "negatif";
        $full_object->jours_arret_travail      = "1";
        $full_object->conclusion               = "dsbsfbsfbyzfeyer i jzoeiujd aoeijd";
        $array_result_full_object              = [
            "exam_general"      => [
                'auscultation_cardio_pulm' => 'anomalie',
                'evenements_anterieurs'    => 'exenements anterieurs',
                'examen_seins'             => 'normal',
                'circulation_veineuse'     => 'insmod',
                'rques_examen_general'     => 'zqfdgh njffgs  fdsvds',
                'oedeme_membres_inf'       => '1',
            ],
            "exam_genico"       => [
                "bruit_du_coeur"         => "percu",
                "presentation_position"  => "tra",
                "col_normal"             => "n",
                "presentation_etat"      => "fix",
                "longueur_col"           => "long",
                "segment_inferieur"      => "namp",
                "position_col"           => "inter",
                "membranes"              => "int",
                "dilatation_col"         => "ferme",
                "bassin"                 => "normal",
                "consistance_col"        => "moy",
                "examen_genital"         => "normal",
                "hauteur_uterine"        => "2",
                "col_commentaire"        => "azertyui",
                "rques_exam_gyneco_obst" => "Lorem ipsum dolor sit amet",
            ],
            "exam_comp"         => [
                "frottis"              => "fait",
                "glycosurie"           => "positif",
                "echographie"          => "nfait",
                "leucocyturie"         => "negatif",
                "prelevement_bacterio" => "nfait",
                "albuminurie"          => "positif",
                "nitrites"             => "negatif",
                "autre_exam_comp"      => "adfeger htr jhytjtyedjxgfdshtfdhs fgs",
                "jours_arret_travail"  => "1",
            ],
            "functionnal_signs" => [
                "metrorragies"           => "0",
                "troubles_digestifs"     => "0",
                "leucorrhees"            => "1",
                "troubles_urinaires"     => "1",
                "contractions_anormales" => "0",
                "autres_anomalies"       => "efzfezz",
                "mouvements_foetaux"     => "1",
                "mouvements_actifs"      => "percu",
                "hypertension"           => "0",
            ],
        ];

        $mid_object                           = new CSuiviGrossesse();
        $mid_object->auscultation_cardio_pulm = "anomalie";
        $mid_object->evenements_anterieurs    = "exenements anterieurs";
        $mid_object->metrorragies             = "0";
        $mid_object->troubles_digestifs       = "0";
        $mid_object->leucorrhees              = "1";

        $array_result_mid_object = [
            "exam_general"      => [
                'auscultation_cardio_pulm' => 'anomalie',
                'evenements_anterieurs'    => 'exenements anterieurs',
            ],
            "exam_genico"       => [],
            "exam_comp"         => [],
            "functionnal_signs" => ["metrorragies" => "0", "troubles_digestifs" => "0", "leucorrhees" => "1"],
        ];

        $empty_object              = new CSuiviGrossesse();
        $array_result_empty_object = [
            "exam_general"      => [],
            "exam_genico"       => [],
            "exam_comp"         => [],
            "functionnal_signs" => [],
        ];

        return [
            'full_object'  => [$full_object, $array_result_full_object],
            'mid_object'   => [$mid_object, $array_result_mid_object],
            'empty_object' => [$empty_object, $array_result_empty_object],
        ];
    }
}
