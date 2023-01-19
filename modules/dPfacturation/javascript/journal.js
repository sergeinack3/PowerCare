/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Journal = {
  viewJournal : function (type) {
    var oForm = document.printFrm;
    new Url('facturation', 'vw_journal')
      .addParam('type'            , type)
      .addParam('prat_id'         , oForm.chir.value)
      .addParam('suppressHeaders' , '1')
      .addElement(oForm._date_min)
      .addElement(oForm._date_max)
      .popup(1000, 600);
  },
  filesJournal : function() {
    var form = document.printFrm;
    var formFiles = document.printFiles;
    new Url("facturation", "ajax_vw_files")
      .addParam("type_journal", formFiles.type.value)
      .addElement(form._date_min)
      .addElement(form._date_max)
      .requestUpdate('files_journaux');
  }
};
