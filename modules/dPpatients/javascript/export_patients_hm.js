/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExportPatientsHm = window.ExportPatientsHm || {


  nextImport: function () {
    var form = getForm("do-export-patients-hm");

    if (!$V(form['continue'])) {
      return;
    }

    form.onsubmit();
  },

  removeFile: function () {
    var url = new Url('dPpatients', 'ajax_hm_remove_file');
    url.requestUpdate('export-hm-remove-file');
  }
};