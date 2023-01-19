{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=retrocession ajax=true}}

<div id="list_retrocessions">
  <button type="button" class="add" onclick="Retrocession.edit('0');">{{tr}}CRetrocession-title-create{{/tr}}</button>
  <form action="?" name="selectPrat_retrocessions" method="get">
    <select name="prat_id" onchange="Retrocession.refreshList(this.value);">
      {{if $listPrat|@count > 1}}
      <option value="0">&mdash; {{tr}}compta-none_prat{{/tr}}</option>
      {{/if}}
      {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat selected=$praticien->_id}}
    </select>
  </form>

  {{if !$praticien->_id}}
    <div class="small-info">{{tr}}CMediusers-select-praticien{{/tr}}</div>
  {{else}}
    <table class="main tbl">
      <tr>
        <th colspan="7" class="title">{{tr}}CRetrocession.all{{/tr}} {{tr}}from{{/tr}} {{$praticien->_view}}</th>
      </tr>
      <tr>
        <th>{{mb_title class=CRetrocession field=nom}}</th>
        <th class="narrow">{{mb_title class=CRetrocession field=code_class}}</th>
        <th>{{mb_title class=CRetrocession field=code}}</th>
        <th>{{mb_title class=CRetrocession field=type}}</th>
        <th>{{mb_title class=CRetrocession field=valeur}}</th>
      </tr>
      {{foreach from=$praticien->_ref_retrocessions item=_retrocession}}
      <tr style="text-align:center;">
        <td><a href="#" onclick="Retrocession.edit('{{$_retrocession->_id}}');">{{mb_value object=$_retrocession field=nom}}</a></td>
        <td>{{mb_value object=$_retrocession field=code_class}}</td>
        <td>{{mb_value object=$_retrocession field=code}}</td>
        <td>{{mb_value object=$_retrocession field=type}}</td>
        {{if $_retrocession->type != "autre"}}
          {{if $_retrocession->type == "montant"}}
            <td>{{mb_value object=$_retrocession field=valeur}}</td>
          {{else}}
            <td>{{$_retrocession->valeur}} %</td>
          {{/if}}
        {{else}}
          <td></td>
        {{/if}}
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="7" class="empty">{{tr}}CRetrocession.none{{/tr}}</td>
      </tr>
      {{/foreach}}
    </table>
  {{/if}}
</div>