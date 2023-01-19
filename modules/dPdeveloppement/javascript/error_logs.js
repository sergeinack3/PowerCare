/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ErrorLogs = window.ErrorLogs || {
  filterError: function () {
    let type = $V('source-type');
    let url = null;
    if (type === "elastic") {
      url = new Url("developpement", "listErrorLogsElastic");
    } else {
      url = new Url("developpement", "listErrorLogs");
    }
    url.addFormData(getForm("filter-error"));
    url.requestUpdate("error-list");
    return false;
  },

  jsonViewer: function (infos) {
    new Url("developpement", "showLogInfos")
      .addParam('json', infos)
      .requestModal(800, 500, {
        method:        'post',
        showReload:    false,
        getParameters: {m: 'developpement', a: 'showLogInfos'}
      });
  },

  filterLog: function () {
    document.body.style.cursor = "wait";

    const waring_file = document.getElementById("div-warning-local-logging-file");
    const file_info = document.getElementById("application-log-file-info");
    const elastic_info = document.getElementById("application-log-elastic-info");

    var url = "";
    if ($V("elasticsearch-or-file") === "elasticsearch") {
      url = new Url("developpement", "listApplicationLogUsingElastic");
      waring_file.style.display = "none";
      if (file_info) {
        file_info.style.display = "none";
      }
      if (elastic_info) {
        elastic_info.style.display = "block";
      }
    } else {
      url = new Url("developpement", "listApplicationLogUsingFile");
      document.getElementById("div-warning-local-logging-file").style.display = "block";
      if (file_info) {
        file_info.style.display = "block";
      }
      if (elastic_info) {
        elastic_info.style.display = "none";
      }
    }
    url.addFormData(getForm("filter-log"));
    url.requestHTML(function (html) {
      var parent = document.getElementById('log-list');
      var divs = document.getElementsByClassName('divShowMoreLog');
      for (var pas = 0; pas < divs.length; pas++) {
        divs[pas].remove();
      }
      parent.insert(html);
      document.body.style.cursor = "auto";
    });

    var log_start = parseInt(document.getElementById('log_start').value) + 1000;
    document.getElementById('log_start').value = log_start;

    return false;
  },

  showMoreLog: function (element) {
    var i = element.querySelector('i');
    var class_list = i.classList;
    class_list.remove('fa-arrow-circle-down');
    class_list.add('fa-spinner');
    class_list.add('fa-spin');
    ErrorLogs.filterLog();
  },

  refreshLog: function () {
    document.getElementById('log-list').innerHTML = '';
    document.getElementById('log_start').value = 0;
    let grep_search = document.getElementById('grep_search');
    if (grep_search) {
      grep_search.value = '';
    }

      ErrorLogs.filterLog();
    },

  grepLog: function () {
    var grep_len = document.getElementById('grep_search').value.length;

    if (grep_len > 0 && grep_len < 3) {
      alert('La recherche doit dépasser 3 caractères.');
      return false;
    }

    document.getElementById('log-list').innerHTML = '';
    document.getElementById('log_start').value = 0;
    ErrorLogs.filterLog();
    return false;
  },

  toggleCheckboxes: function (checkbox) {
    var form = getForm("filter-error");

    checkbox.next('fieldset').select('input.type').invoke("writeAttribute", "checked", checkbox.checked);

    $V(form.start, 0);
  },

  removeLogs: function () {
    if ($V("elasticsearch-or-file") === "elasticsearch") {
      if (confirm('Voulez-vous vider complètement l\'index Elasticseach de log ?')) {
        new Url("developpement", "deleteApplicationLogElasticsearchIndex")
          .requestUpdate('log-list');
      }
    } else {
      if (confirm('Voulez-vous vider complètement le journal de log ?')) {
        new Url("developpement", "deleteApplicationLogFile")
          .requestUpdate('log-list');
      }
    }
  },

  listErrorLogWhitelist: function () {
    new Url("developpement", "ajax_list_error_log_whitelist")
      .requestModal(800, 600);
  },

  toogleErrorLogWhitelist: function (error_log_id) {
    let type = $V('source-type');

    new Url("developpement", "ajax_toogle_error_log_whitelist")
      .addParam('error_log_id', error_log_id)
      .addParam('is_elastic_log', type === "elastic")
      .requestUpdate('systemMsg', {
        onComplete: function () {
          document.getElementById("btn-search-errors").click();
        }
      });
  },
};
