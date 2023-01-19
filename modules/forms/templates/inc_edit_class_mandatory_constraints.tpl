{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.setTabCount('event-mandatory-constraints', '{{$constraints|@count}}')
  });
</script>

{{if !$ex_class_event->hostHasMandatoryFields()}}
  <div class="small-warning">
    {{tr}}CExClassEvent-msg-This object cannot have mandatory constraint|pl.{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="5">
      <button type="button" class="new" style="float: right;" onclick="ExMandatoryConstraint.create({{$ex_class_event->_id}})">
        {{tr}}CExClassConstraint-title-create{{/tr}}
      </button>

      {{tr}}CExClassEvent-back-mandatory_constraints{{/tr}}
    </th>
  </tr>

  <tr>
    <th>{{mb_title class=CExClassMandatoryConstraint field=field}}</th>
    <th>{{mb_title class=CExClassMandatoryConstraint field=operator}}</th>
    <th>{{mb_title class=CExClassMandatoryConstraint field=value}}</th>
    <th>{{mb_title class=CExClassMandatoryConstraint field=reference_value}}</th>
    <th>{{mb_title class=CExClassMandatoryConstraint field=comment}}</th>
  </tr>

  {{foreach from=$constraints item=_constraint}}
    <tr data-constraint_id="{{$_constraint->_id}}">
      <td class="text">
        <a href="#1" onclick="ExMandatoryConstraint.edit({{$_constraint->_id}}); return false;">
          {{$_constraint}}
        </a>
      </td>

      <td style="text-align: center;">{{mb_value object=$_constraint field=operator}}</td>

      <td class="text">
        {{if $_constraint->operator == "in"}}
          {{assign var=_values value=$_constraint->getInValues()}}
          {{foreach from=$_values item=_value name=_in}}
            <span style="background: #ddd; padding: 0 2px; white-space: nowrap;">{{$_value}}</span>{{if !$smarty.foreach._in.last}},{{/if}}
          {{/foreach}}
        {{else}}
          {{$_constraint->formatValue()}}
        {{/if}}
      </td>

      <td>{{mb_value object=$_constraint field=reference_value}}</td>

      <td class="text" {{if $_constraint->comment}} title="{{mb_value object=$_constraint field=_formulae}}"{{/if}}>
        {{if $_constraint->comment}}
          {{mb_value object=$_constraint field=comment}}
        {{else}}
          {{mb_value object=$_constraint field=_formulae}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CExClassMandatoryConstraint.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

<div class="small-info">
  Les contraintes d'obligation permettent de définir dans quelles conditions et à quel moment les formulaires seront considérés comme devant <strong>être impérativement remplis</strong>.
</div>
