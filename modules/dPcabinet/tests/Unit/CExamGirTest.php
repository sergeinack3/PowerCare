<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Mediboard\Cabinet\CExamGir;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CExamGirTest
 *
 * @package Ox\Mediboard\Cabinet\Tests\Unit
 */
class CExamGirTest extends OxUnitTestCase {

  public function computeScoreGirProvider() {
    return [
      'test_score_gir_1' => [
        [
          "coherence"        => "C",
          "orientation"      => "C",
          "toilette"         => "C",
          "habillage"        => "C",
          "alimentation"     => "C",
          "elimination"      => "C",
          "transferts"       => "C",
          "deplacements_int" => "C",
          "deplacements_ext" => "C",
          "alerter"          => "C",
        ], 1
      ],
      'test_score_gir_2' => [
        [
          "coherence"        => "C",
          "orientation"      => "C",
          "toilette"         => "C",
          "habillage"        => "C",
          "alimentation"     => "A",
          "elimination"      => "A",
          "transferts"       => "A",
          "deplacements_int" => "A",
          "deplacements_ext" => "A",
          "alerter"          => "A",
        ], 2
      ],
      'test_score_gir_3' => [
        [
          "coherence"        => "A",
          "orientation"      => "A",
          "toilette"         => "C",
          "habillage"        => "C",
          "alimentation"     => "A",
          "elimination"      => "A",
          "transferts"       => "A",
          "deplacements_int" => "A",
          "deplacements_ext" => "A",
          "alerter"          => "A",
        ], 3],
      'test_score_gir_4' => [
        [
          "coherence"        => "A",
          "orientation"      => "A",
          "toilette"         => "B",
          "habillage"        => "C",
          "alimentation"     => "A",
          "elimination"      => "A",
          "transferts"       => "A",
          "deplacements_int" => "A",
          "deplacements_ext" => "A",
          "alerter"          => "A",
        ], 4],
      'test_score_gir_5' => [
        [
          "coherence"        => "A",
          "orientation"      => "A",
          "toilette"         => "A",
          "habillage"        => "B",
          "alimentation"     => "A",
          "elimination"      => "A",
          "transferts"       => "A",
          "deplacements_int" => "A",
          "deplacements_ext" => "A",
          "alerter"          => "A",
        ], 5],
      'test_score_gir_6' => [
        [
          "coherence"        => "A",
          "orientation"      => "A",
          "toilette"         => "A",
          "habillage"        => "A",
          "alimentation"     => "A",
          "elimination"      => "A",
          "transferts"       => "A",
          "deplacements_int" => "A",
          "deplacements_ext" => "A",
          "alerter"          => "A",
        ], 6],
    ];
  }

  /**
   * Test du calcul du Score Gir - Résultat attendu - 1
   * @dataProvider computeScoreGirProvider
   */
  public function testComputeScoreGir(array $test_codages, int $expected) {
    // Score GIR = 1
    $test_score_gir = new CExamGir();
    $actual         = $test_score_gir->computeScoreGir(0, $test_codages);
    $this->assertEquals($expected, $actual);
  }
}
