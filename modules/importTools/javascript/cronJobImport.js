/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CronJobImport = {
  changePage: function(page, changePage) {
    var args = changePage.split('|');

    var url = new Url('importTools', 'ajax_filter_logs');
    url.addParam('import_mod_name', args[1]);
    url.addParam('import_class_name', args[2]);
    url.addParam('date_log_min', args[3]);
    url.addParam('date_log_max', args[4]);
    url.addParam('type', args[0]);
    url.addParam('start', page);

    url.requestUpdate('tab_log_' + args[0]);
  }
};