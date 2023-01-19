/**
 * @package Mediboard\openData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ImportHospidiag = window.ImportHospidiag || {
  editHDEtablissement : function (HDEtablissement_id,callback) {
    var url = new Url('openData','ajax_edit_HDEtablissement');
    url.addParam('HDEtablissement_id', HDEtablissement_id);

    url.requestModal(800, 400,callback);
  }
};