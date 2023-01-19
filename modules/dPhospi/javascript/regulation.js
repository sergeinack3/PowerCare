/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Regulation = {
  showDossierSoins: function (sejour_id, date, default_tab) {
    var url = new Url("soins", "viewDossierSejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("modal", 1);
    if (default_tab) {
      url.addParam("default_tab", default_tab);
    }
    url.requestModal("95%", "90%", {
      showClose: false
    });
    modalWindow = url.modalObject;
  },
  searchSejours:    function (form) {
    var url = new Url("hospi", "regulationView");
    url.addFormData(form);
    url.addParam('see_results', 1);
    url.addParam("services_id", [$V(form.services_id)].flatten().join(","));
    url.addParam("type", [$V(form.type)].flatten().join(","));
    url.requestUpdate('list_sejours_regulation');
  }
};
