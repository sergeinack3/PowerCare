/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
GenericImport = window.GenericImport || {
  showDetails: function (class_name) {
    var url = new Url('genericImport', 'vw_detail_import_class');
    url.addParam('class_name', class_name);
    url.requestModal();
  },

  downloadFile: function (class_name) {
    var url = new Url('genericImport', 'ajax_download_file', 'raw');
    if (class_name) {
      url.addParam('class_name', class_name);
    }

    url.popup();
  },

  downloadInfos: function (class_name) {
    var url = new Url('genericImport', 'ajax_download_file_infos', 'raw');
    url.addParam('class_name', class_name);
    url.popup();
  },

  listFilesModal: function (form) {
    var url = new Url('genericImport', 'vw_files_for_campaign');
    url.addParam('import_campaign_id', $V(form.import_campaign_id));
    url.requestModal();
  },

  listImportFiles: function () {
    var url = new Url('genericImport', 'vw_files_for_campaign');
    url.addParam('import_campaign_id', $V($('import-campaign-select')));
    url.addParam('update', '1');
    url.requestUpdate('result-list-files-for-campaign');
  }
};
