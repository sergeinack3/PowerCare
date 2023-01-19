{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-align">
  <tr>
    <th colspan="6" class="title">
      <button class="new notext" onclick="Remplacement.edit(0, '{{$user->_id}}')" style="float: left;"
              title="{{tr}}CRemplacement-title-create{{/tr}}"></button>

      {{tr}}CRemplacement|pl{{/tr}}: {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}

      <form name="change_hide_old" action="#" method="post" onsubmit="return false;" style="float: right;">
        <label title="{{tr}}CRemplacement.hide_old.desc{{/tr}}">
          <input type="checkbox" name="hide_old" value="{{$hide_old}}" {{if $hide_old}}checked="checked"{{/if}}
            onchange="Remplacement.refreshList('{{$user->_id}}', this.checked ? 1 : 0)"/>
          {{tr}}CRemplacement.hide_old{{/tr}}
        </label>
      </form>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
    <th style="width: 20%">{{tr}}Dates{{/tr}}</th>
    <th style="width: 20%">{{mb_title class=CRemplacement field=remplace_id}}</th>
    <th style="width: 20%">{{mb_title class=CRemplacement field=remplacant_id}}</th>
    <th>{{mb_title class=CRemplacement field=libelle}}</th>
    <th>{{mb_title class=CRemplacement field=description}}</th>
  </tr>
  {{foreach from=$remplacements item=_remplacement}}
    <tr {{if $_remplacement->fin < $dtnow}}class="hatching"{{/if}}>
      <td class="button">
        <button class="edit notext" onclick="Remplacement.edit('{{$_remplacement->_id}}', '{{$user->_id}}')"
                title="{{tr}}CRemplacement-title-modify{{/tr}}"></button>
      </td>
      <td>
        {{mb_include module=system template=inc_interval_datetime from=$_remplacement->debut to=$_remplacement->fin}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_remplacement->_ref_remplace->_guid}}');"
              class="mediuser" style="border-left-color: #{{$_remplacement->_ref_remplace->_color}};">
          {{mb_ditto name=remplace_id value=$_remplacement->_ref_remplace}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_remplacement->_ref_remplacant->_guid}}');"
              class="mediuser" style="border-left-color: #{{$_remplacement->_ref_remplacant->_color}};">
          {{mb_ditto name=remplacant_id value=$_remplacement->_ref_remplacant}}
        </span>
      </td>
      <td>{{mb_value object=$_remplacement field=libelle}}</td>
      <td>{{mb_value object=$_remplacement field=description}}</td>
    </tr>
    {{foreachelse}}
      <tr>
        <td colspan="6" class="empty">{{tr}}CRemplacement.none{{/tr}}</td>
      </tr>
  {{/foreach}}
</table>