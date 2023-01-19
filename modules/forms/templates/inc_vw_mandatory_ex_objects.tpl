{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{$object}}</h2>

<table class="main tbl">
  <tr>
    <th>{{tr}}CExClass{{/tr}}</th>
    <th>{{tr}}CExClassEvent|pl{{/tr}}</th>
    <th>{{tr}}CExClassMandatoryConstraint|pl{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$ex_events key=_ex_class_id item=_ex_events}}
    {{foreach name=mandatory from=$_ex_events item=_ex_event}}
      <tr>
        {{if $smarty.foreach.mandatory.first}}
          <td class="text" rowspan="{{$_ex_events|@count}}">{{$_ex_event->_ref_ex_class}}</td>
        {{/if}}

        <td class="text">{{$_ex_event}}</td>

        <td class="text">
          {{foreach name=cons from=$_ex_event->_ref_mandatory_constraints item=_constraint}}
            <div {{if $_constraint->comment}} title="{{$_constraint->_formulae}}"{{/if}}>
              {{if $_constraint->comment}}
                {{mb_value object=$_constraint field=comment}}
              {{else}}
                {{mb_value object=$_constraint field=_formulae}}
              {{/if}}
            </div>

            {{if !$smarty.foreach.cons.last}}
              <hr />
            {{/if}}
          {{/foreach}}
        </td>

        <td>
          <button type="button" class="new-lightning notext compact" onclick="
            ExObject.onAfterSave = function() { ExObject.urlMandatoryConstraints.refreshModal(); };

            showExClassForm(
            '{{$_ex_class_id}}',
            '{{$object->_class}}-{{$object->_id}}',
            '{{$object->_class}}-{{$_ex_event->event_name}}',
            null,
            '{{$_ex_event->event_name}}',
            );
            ">
            Nouveau formulaire
          </button>
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>