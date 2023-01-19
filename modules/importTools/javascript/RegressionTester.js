/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RegressionTester = {

  showDatas: function(table_id) {
    var table = $(table_id);
    if (!table) {
      return null;
    }
    if (table.style.display === 'none') {
      table.style.display = 'block';
    }
    else {
      table.style.display = 'none';
    }
  },

  compareClasses: function(class_name, tag) {
    var url = new Url('importTools', 'ajax_pop_class_diff');
    url.addParam('class_name', class_name);
    url.addParam('tag', tag);
    url.requestModal('50%', '50%');
  }
};