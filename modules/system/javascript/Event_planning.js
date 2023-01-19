/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * planning tool for view
 */
EventPlanning = Class.create({

  initialize: function(guid, hour_min, hour_max) {
    this.guid = guid;
    this.hour_min = hour_min;
    this.hour_max = hour_max;

  },

  onMenuClick: function(event, data, elem){
    console.log(event, data);
  }
});