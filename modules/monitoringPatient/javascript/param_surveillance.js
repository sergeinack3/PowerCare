/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ParamSurveillance = {
  edit:           function (param_guid) {
    new Url("monitoringPatient", "ajax_edit_config_param_surveillance")
      .addParam("param_guid", param_guid)
      .requestModal(500, 430, {
        onClose: ParamSurveillance.list.curry(param_guid.split(/-/)[0])
      });
  },
  list:           function (object_class, start) {
    new Url("monitoringPatient", "ajax_list_config_param_surveillance")
      .addParam("object_class", object_class)
      .addParam("start", start)
      .requestUpdate("list-" + object_class);
  },
  listConversion: function (start) {
    new Url('monitoringPatient', 'ajax_list_observation_value_to_constant')
      .addParam('start', start)
      .requestUpdate('list-CObservationValueToConstant');
  },

  editConversion: function (object_id) {
    new Url('monitoringPatient', 'ajax_edit_observation_value_to_constant')
      .addParam('observation_value_to_constant_id', object_id)
      .requestModal(500, 360, {
        onClose: ParamSurveillance.listConversion.curry()
      });
  },
};
