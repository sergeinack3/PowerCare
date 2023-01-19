{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  {{foreach from=$ex_objects_counts key=_ex_class_id item=_ex_class_count}}
    <tr id="row-ex_class-{{$_ex_class_id}}">
      <td>
        {{assign var=_ex_class value=$ex_classes.$_ex_class_id}}
        
        <div style="float: right;">
          <span {{if $_ex_class->conditional}}style="background: #7e7;" title="{{tr}}CExClass-conditional{{/tr}}"{{/if}}>&nbsp;</span>
        </div>
        
        <a href="#1" onclick="ExObject.loadExObjects(null, null, $('list-ex_object'), 2, {{$_ex_class_id}}, Object.extend(getForm('filter-ex_object').serialize(true), {a: 'ajax_list_ex_object'})); $('row-ex_class-{{$_ex_class_id}}').addUniqueClassName('selected'); return false;">
          {{$_ex_class->name}}
        </a>
      </td>
      <td class="narrow" style="text-align: right;">
        {{$_ex_class_count}} 
        <button class="download notext compact"
                onclick="ExObject.exportCSV({{$_ex_class_id}}, getForm('filter-ex_object').serialize(true))" title="Export au format CSV des 10000 premières lignes"></button>
        <button class="right notext compact"
                onclick="ExObject.loadExObjects(null, null, $('list-ex_object'), 2, {{$_ex_class_id}}, Object.extend(getForm('filter-ex_object').serialize(true), {a: 'ajax_list_ex_object'})); $('row-ex_class-{{$_ex_class_id}}').addUniqueClassName('selected')"></button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}CExObject.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
