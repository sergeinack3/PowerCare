{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Category summary -->

<h1>Statistiques</h1>

<table class="tbl">
  <tr>
    <th class="title" rowspan="2">{{mb_label class=CFilesCategory field=nom}}</th>
    <th class="title" rowspan="2">{{mb_title class=CFilesCategory field=class}}</th>
    <th class="title" colspan="2">{{tr}}CFilesCategory-back-categorized_files{{/tr}}</th>
    <th class="title" colspan="2">{{tr}}CFilesCategory-back-categorized_documents{{/tr}}</th>
  </tr>

  <tr>
    <th>{{tr}}Unsent{{/tr}}</th>
    <th>{{tr}}Total {{/tr}}</th>
    <th>{{tr}}Unsent{{/tr}}</th>
    <th>{{tr}}Total {{/tr}}</th>
  </tr>

  {{foreach from=$categories item=_category}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_category->_guid}}')">
        {{mb_value object=$_category field=nom}}
      </span>
    </td>
    <td>{{tr}}{{$_category->class}}{{/tr}}</td>
    <td style="text-align: center">{{$_category->_count_unsent_files}}</td>
    <td style="text-align: center">{{$_category->_count_files}}</td>
    <td style="text-align: center">{{$_category->_count_unsent_documents}}</td>
    <td style="text-align: center">{{$_category->_count_documents}}</td>
  </tr>
  {{/foreach}}
</table>

<!-- DocItems detail -->

<h1>Envois</h1>

{{if "dPfiles CDocumentSender system_sender"|gconf}}
<a class="button send" href="?m={{$m}}&{{$actionType}}={{$action}}&do=1">
  {{tr}}Send-upto{{/tr}} {{$max_send}} {{tr}}CDocumentItem{{/tr}}
</a>
{{else}}
<div class="small-warning">
  {{tr}}dPfiles-system_sender-undefined{{/tr}}
</div>
{{/if}}

<table class="tbl">
{{foreach from=$items item=_items key=class}} 
  <tr>
    <th class="title" colspan="10">
      {{tr}}{{$class}}{{/tr}}
      <small>
        ({{$_items|@count}} chargés / {{$count.$class}} disponibles)
      </small>
    </th>
  </tr>
  
  <tr>
    <th>{{mb_title class=$class field=file_category_id}}</th>
    <th>{{mb_title class=$class field=object_id}}</th>
    <th>{{mb_title class=$class field=_extensioned}}</th>
    <th>{{mb_title class=$class field=etat_envoi}}</th>
    <th>{{mb_title class=$class field=_send_problem}}</th>
  </tr>
  
  {{foreach from=$_items item=_item}}
  <tr>
    <td>
      {{assign var=category_id value=$_item->file_category_id}}
      {{$categories.$category_id}}
    </td>
    {{if $_item->_ref_object}}
      <td onmouseover="ObjectTooltip.createEx(this,'{{$_item->_ref_object->_guid}}')">
        {{$_item->_ref_object}}
      </td>
    {{else}}
      <td class="empty">Cible non chargée</td>
    {{/if}}
    <td onmouseover="ObjectTooltip.createEx(this,'{{$_item->_guid}}')">
      {{mb_value object=$_item field=_extensioned}}
    </td>
    <td>{{mb_value object=$_item field=etat_envoi}}</td>
    <td>
      {{if $_item->_send_problem}}
      <div class="{{$_item->_send|ternary:error:warning}}">
      {{mb_value object=$_item field=_send_problem}}
      </div>
      {{else}}
        {{if $_item->_send}}
        <div class="info">
          {{tr}}Sent{{/tr}} !
        </div>
        {{/if}}
      {{/if}}

    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="5" class="empty">{{tr}}{{$class}}.none{{/tr}}</td>
  </tr>
  {{/foreach}}
{{/foreach}}
</table>

