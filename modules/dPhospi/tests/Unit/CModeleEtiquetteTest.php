<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Tests\Unit;

use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Tests\OxUnitTestCase;

class CModeleEtiquetteTest extends OxUnitTestCase {
  static $texte = "#8DATE COURANTE#\n*8HEURE COURANTE*";

  public function getModeleEtiquetteFilled() {
    $modele_etiquette = new CModeleEtiquette();
    $modele_etiquette->largeur_page = $modele_etiquette->hauteur_page = 10;
    $modele_etiquette->nb_colonnes  = $modele_etiquette->nb_lignes = 2;
    $modele_etiquette->texte = self::$texte;
    $fields = array();
    $modele_etiquette->completeLabelFields($fields, null);
    $modele_etiquette->replaceFields($fields);
    return $modele_etiquette;
  }

  public function test__construct() {
    $modele_etiquette = new CModeleEtiquette();
    $this->assertInstanceOf(CModeleEtiquette::class, $modele_etiquette);
  }

  public function testUpdateFormFields() {
    $modele_etiquette = new CModeleEtiquette();
    $modele_etiquette->largeur_page = $modele_etiquette->hauteur_page = 10;
    $modele_etiquette->nb_colonnes  = $modele_etiquette->nb_lignes = 2;
    $modele_etiquette->updateFormFields();
    $this->assertEquals(5, $modele_etiquette->_width_etiq);
    $this->assertEquals(5, $modele_etiquette->_height_etiq);
  }

  public function testReplaceFields() {
    $modele_etiquette = $this->getModeleEtiquetteFilled();
    $this->assertNotEquals(self::$texte, $modele_etiquette->texte);
  }

  public function testPrintEtiquettes() {
    $modele_etiquette = $this->getModeleEtiquetteFilled();
    $this->assertStringContainsString("%PDF", $modele_etiquette->printEtiquettes(null, 0));
  }
}
