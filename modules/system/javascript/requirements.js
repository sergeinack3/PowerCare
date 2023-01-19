/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

requirements = {
  updateTitleStatus(tab, group, count_errors) {
    if (isNaN(count_errors) || count_errors === 0) {
      return;
    }

    var element_group = document.querySelector(`#a_${tab}_${group}`);
    if (element_group) {
      element_group.classList.add("wrong");
    }

    var element_tab = document.querySelector(`#a_${tab}`);
    if (element_tab) {
      element_tab.classList.add("wrong");
    }
  },

  changeGroup(form, module) {
    var group_id = $V(form.group_id);
    new Url('system', 'vw_requirements').addParam('group_id', group_id).addParam('mod_name', module).requestUpdate('modal-container-requirements');
  }
};