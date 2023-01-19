{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sejour->_ref_operations|@count}}
  {{mb_return}}
{{/if}}

<fieldset>
  <legend>Interventions du séjour</legend>

  <ul>
    {{foreach from=$sejour->_ref_operations item=operation}}
    <li>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}');">
        {{$operation}}
      </span>
    </li>
    {{/foreach}}
  </ul>
</fieldset>