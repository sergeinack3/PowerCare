{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}system-msg-Pseudonymise fields to modify{{/tr}} :
  <ul>
    <li>{{tr}}CFunctions-text{{/tr}} : Modifi� en "Fonction XX" avec XX un nombre</li>
  </ul>

  <br/>

  {{if $_fields}}
    {{mb_include module=system template="pseudonymise/inc_other_fields"}}
  {{/if}}
</div>