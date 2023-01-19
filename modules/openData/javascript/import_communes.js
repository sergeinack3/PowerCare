/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ImportCommunes = window.ImportCommunes || {
  nextImportCommunesFrance: function() {
    var form = getForm("import-communes-france");

    if (!$V(form['continue'])) {
      return;
    }

    form.onsubmit();
  }
};