/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Alert = {
  object_guid: null,
  tag: null,
  level: null,
  element: null,
  callback: null,

  showAlerts: function(object_guid, tag, level, callback, element) {
    Alert.object_guid = object_guid;
    Alert.tag = tag;
    Alert.level = level;
    Alert.callback = callback || Prototype.emptyFunction;
    Alert.element = element;

    ObjectTooltip.modes.alert = {
      module: 'system',
      action: 'ajax_vw_alertes',
      sClass: 'tooltip'
    };

    ObjectTooltip.createEx(
      element, object_guid, 'alert',
      {
        level: level,
        tag: tag,
      },
      {
        duration: 0
      }
    );
  }
};