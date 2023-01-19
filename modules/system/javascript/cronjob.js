/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CronJob = {
  edit: function (identifiant) {
    new Url("system", "ajax_edit_cronjob")
      .addParam("identifiant", identifiant)
      .requestModal()
      .modalObject.observe("afterClose", CronJob.refresh_list_cronjobs);
  },

  refresh_list_cronjobs: function () {
    new Url("system", "ajax_list_cronjobs")
      .requestUpdate("list_cronjobs");
  },

  refresh_log_cronjobs: function () {
    new Url("system", "ajax_cronjobs_logs")
      .requestUpdate("search_log_cronjob");
  },

  changeField: function (element) {
    var value = true;
    if ($V(element) === "") {
      value = false;
    }
    var form = element.form;
    form._second.disabled = value;
    form._minute.disabled = value;
    form._hour.disabled = value;
    form._day.disabled = value;
    form._month.disabled = value;
    form._week.disabled = value;
  },

  refresh_logs: function (form) {
    new Url("system", "ajax_cronjobs_logs")
      .addFormData(form)
      .requestUpdate("search_log_cronjob");
  },

  refresh_list: function (form) {
    new Url("system", "ajax_list_cronjobs")
      .addFormData(form)
      .requestUpdate("list_cronjobs");
  },

  changePageLog: function (page) {
    var form = getForm("search_cronjob");
    $V(form.page, page);
    CronJob.refresh_logs(form);
  },

  changePageList: function (page) {
    var form = getForm("search_cronjob_list");
    $V(form.page, page);
    CronJob.refresh_list(form);
  },


  ChangeActive: function (radio_button) {

    var list_elements_tp = radio_button.up(1).children;

    if (radio_button[2].checked == false && radio_button[3].checked == true) {

      for (const element of list_elements_tp) {
        element.style.opacity = "30%";
      }

      list_elements_tp[0].style.opacity = "100%";

    } else {

      for (const element of list_elements_tp) {
        element.style.opacity = "100%";
      }
    }

  },

  setServerAddress: function (element) {
    var tokenfield = new TokenField(element.form.servers_address);
    if ($V(element)) {
      tokenfield.add(element.value);
    } else {
      tokenfield.remove(element.value);
    }
  }
};
