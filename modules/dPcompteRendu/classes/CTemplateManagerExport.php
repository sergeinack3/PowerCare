<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\FileUtil\CCSVFile;
use Exception;

/**
 * Export des champs de modèle
 */
class CTemplateManagerExport implements IShortNameAutoloadable {
  /** @var CMbObject $object */
  protected $object;

  /**
   * CTemplateManagerExport constructor.
   *
   * @param CMbObject $object Objet concerné
   */
  public function __construct($object) {
    $this->object = $object;
  }

  /**
   * Export des champs
   *
   * @return void
   */
  public function export() {
    if (!$this->object || !method_exists($this->object, "fillTemplate")) {
      throw new Exception(CAppUI::tr("CTemplateManager-Error fetching fields"));
    }

    $template = new CTemplateManager();

    // Complétion des champs de modèles sur le template
    $this->object->fillTemplate($template);

    $csv = new CCSVFile();

    foreach ($template->sections as $_section => $_fields) {
      $csv->writeLine([$_section]);

      foreach ($_fields as $_field => $_subfields) {
        $_split_field = $_field;

        // Retrait du nom de section dans le nom du champ (format Section - Champ)
        if (strpos($_field, "-") !== false) {
          list($_split_section, $_split_field) = explode(" - ", $_field);
        }

        $csv->writeLine(["", $_split_field]);

        // Si c'est un champ (présence de la clé field), alors pas de sous-champs disponibles
        if (array_key_exists("field", $_subfields)) {
          continue;
        }

        foreach ($_subfields as $_subfield => $__subfield) {
          $csv->writeLine(["", "", $_subfield]);
        }
      }
    }

    $csv->stream(CAppUI::tr($this->object->_class));
  }
}
