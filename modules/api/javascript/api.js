/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

api = {
  searchMobileLogMultiCriteria: function (form) {
    new Url("api", "ajax_search_mobile_log")
      .addFormData(form)
      .requestUpdate("result_search_mobile_log");

    return false;
  },

  viewMobileLog: function (mobile_log_guid) {
    new Url('api', 'ajax_view_mobile_log_details')
      .addParam("mobile_log_guid", mobile_log_guid)
      .requestModal("75%", "90%");
  },


  modalResponseRequest: function (form, stack_request_id) {
    new Url("api", "modal_response_request")
      .addParam("stack_request_id", stack_request_id)
      .addFormData(form)
      .requestModal("50%", null);
  },

  sendRequests: function (form) {
    var class_api = [];
    for (var i = 0; i < form.elements.length; i++) {
      var elem = form.elements[i];
      if (elem.nodeName === "INPUT" && elem.checked) {
        class_api.push(elem.name.split("_")[2])
      }
    }

    new Url("api", "controllers/do_send_requests")
      .addFormData(form)
      .requestUpdate("systemMsg", {
        onComplete: function () {
          class_api.forEach(function (classname) {
            new Url("api", "ajax_refresh_stack")
              .addParam("api_classname", classname)
              .requestUpdate("requests_" + classname);
          });
        }
      });
  },

  sendSelectedRequests: function (api_class) {
    var requests_ids_str = "";
    var form = getForm(api_class+"_request_response");
    $(form.request_checkbox).forEach(function (item) {
      if (item.checked) {
        requests_ids_str += requests_ids_str === "" ? item.id : " " + item.id ;
      }
    });

    var form_filter = getForm("form_class_api_synchronize");
    new Url("api", "controllers/do_send_requests_manually")
      .addParam("api_class_name", api_class)
      .addParam("requests_id_str", requests_ids_str)
      .requestUpdate("systemMsg", {
        onComplete: function () {
          new Url("api", "ajax_refresh_stack")
            .addParam("api_classname", api_class)
            .addFormData(form_filter)
            .requestUpdate("requests_" + api_class);
        }
      });
  },

  sendSelectedRequest: function (api_class, requests_id) {
    new Url("api", "controllers/do_send_requests_manually")
      .addParam("api_class_name", api_class)
      .addParam("requests_id_str", requests_id)
      .requestUpdate("systemMsg", {
        onComplete: function () {
          new Url("api", "ajax_refresh_request")
            .addParam("api_classname", api_class)
            .addParam("request_id", requests_id)
            .requestUpdate("request_id_" + requests_id);
        }
      });
  },

  changePage : function(page, classname) {
    var form = getForm("form_class_api_synchronize");
    new Url("api", "ajax_refresh_stack")
      .addParam("api_classname", classname)
      .addParam("page", page)
      .addFormData(form)
      .requestUpdate("requests_" + classname);
  },

  selectIdUser : function(elt, name_input) {
    elt.form.elements[name_input].value = elt.value;
  },

  filterRequests : function (form) {
    new Url("api", "vw_stack_request_api")
      .addFormData(form)
      .requestUpdate("refresh_tabs")
  },

  clearFiler : function(form) {
    $V(form.constant_code, null);
    $V(form.group_id, null);
    $V(form.datetime_start_da, null);
    $V(form.datetime_start, null);
    $V(form.datetime_end, null);
    $V(form.datetime_end_da, null);
    $V(form.group_id_autocomplete_view, null);
    $V(form.patient_id, null);
    $V(form.patient_id_autocomplete_view, null);
    $V(form.emetteur, null);
    form.emetteur.selectedIndex = 0;
  },

  checkAll: function(form_name) {
    var element =  document.getElementById("select_mode_request");
    var requests, num = element.value;
    var checked = true, from_td = false;
    var color = "#000";
    if (num % 3 === 1) {
      requests = document.getElementsByClassName('data_no_send');
      requests = [].slice.call(requests);
      color = "#15964e"
    }
    else {
      var form = getForm(form_name);
      requests = $(form.request_checkbox);
      if(num % 3 === 0) {
        checked = false;
      } else {
        color = "#006e96"
      }
    }
    element.value = parseInt(num) +1;
    element.setAttribute('style', 'color:'+ color + '!important');
    requests.forEach(function (item) {
        item.checked = checked;
    })
  }
};