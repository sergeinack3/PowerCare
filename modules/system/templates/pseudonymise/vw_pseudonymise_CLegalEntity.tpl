{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}system-msg-Pseudonymise fields to modify{{/tr}} :
  <ul>
    <li>{{tr}}CLegalEntity-name{{/tr}} : Modifié en "LE-Ent XX" avec XX un nombre</li>
    <li>{{tr}}CLegalEntity-code{{/tr}} : Modifié en "LE-XX" avec XX un nombre</li>
  </ul>

  <br/>

  {{if $_fields}}
    {{mb_include template="pseudonymise/inc_other_fields"}}
  {{/if}}
</div>