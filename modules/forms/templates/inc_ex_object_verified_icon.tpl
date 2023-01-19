{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_verified value="n/a"}}
{{if $ex_object->_verified}}
  {{assign var=_verified value=$ex_object->_verified}}
{{/if}}

{{if $_verified == "no"}}
  <i class="fa fa-exclamation-triangle" title="{{tr}}CExObject-msg-Not verified{{/tr}}" style="color: orange; font-size: 1.2em;"></i>
{{elseif $_verified == "yes"}}
  <i class="fa fa-check-square" title="{{tr}}CExObject-msg-Verified{{/tr}}" style="color: limegreen; font-size: 1.2em;"></i>
{{/if}}