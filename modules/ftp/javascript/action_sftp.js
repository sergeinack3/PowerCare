/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SFTP = {
  connexion: function (exchange_source_name) {
    new Url("ftp", "ajaxConnexionSFTP")
      .addParam("exchange_source_name", exchange_source_name)
      .requestModal(500, 400);
  },

  getFiles: function (exchange_source_name) {
    new Url("ftp", "ajaxGetFilesSFTP")
      .addParam("exchange_source_name", exchange_source_name)
      .requestModal(500, 400);
  },

  toggleDisabled : function (input_name, source_name) {
    var form = getForm("editSourceSFTP-"+source_name);
    var input = form.elements[input_name];
    input.disabled ? input.disabled = '' : input.disabled = 'disabled';
  }
};
