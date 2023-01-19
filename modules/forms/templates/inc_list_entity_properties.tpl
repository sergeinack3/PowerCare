{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_id}}
<button class="new" onclick="ExFieldProperty.create('{{$object->_class}}', '{{$object->_id}}', this.form)">{{tr}}CExClassFieldProperty-title-create{{/tr}}</button>

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th style="width: 20%;">{{mb_title class=CExClassFieldProperty field=type}}</th>
    <th class="narrow">{{mb_title class=CExClassFieldProperty field=value}}</th>
    <th class="narrow"></th>
    <th>{{mb_title class=CExClassFieldProperty field=predicate_id}}</th>
  </tr>
  {{foreach from=$object->_ref_properties item=_property}}
    <tr>
      <td>
        <button class="edit notext compact" onclick="ExFieldProperty.edit({{$_property->_id}}, '{{$object->_class}}', '{{$object->_id}}', this.form)">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td style="text-align: right;">{{mb_value object=$_property field=type}}</td>
      <td>
        {{mb_value object=$_property field=_value}}
      </td>
      <td style="{{$_property->type}}: {{$_property->value}};">
        Lorem ipsum
      </td>
      <td>{{mb_value object=$_property field=predicate_id}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CExClassFieldProperty.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
{{/if}}