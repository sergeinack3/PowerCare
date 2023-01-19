/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExportPatients = {
  resetPraticiens: function () {
    $('praticien-count').update(0);

    var formSejour = getForm("export-sejours-form");
    $V(formSejour["praticien_id"], null);

    var formPatients = getForm("export-patients-form");
    $V(formPatients["praticien_id"], null);
  },

  listByFunction: function () {
    this.resetPraticiens();

    const form = getForm('search-praticien-function');
    form.onsubmit();
  },

  updatePraticienCount: function () {
    var list = $V($("praticien_ids"));
    $('praticien-count').update(list.length);

    var formSejour = getForm("export-sejours-form");
    $V(formSejour["praticien_id"], list.join(','));

    var formPatients = getForm("export-patients-form");
    $V(formPatients["praticien_id"], list.join(','));

    $V($("praticien_ids_view"), list.join(","));
  },

  checkDirectory: function (input) {
    var url = new Url("patients", "ajax_check_export_dir");
    url.addParam("directory", $V(input));
    url.requestUpdate("directory-check");
  }
};
