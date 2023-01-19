{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=debiteur ajax=true}}

<div id="list_coeffs">
  <button type="button" class="add" onclick="Coeff.edit('0', '{{$prat->_id}}');">
    {{tr}}CFactureCoeff-title-create{{/tr}}
  </button>
  <form action="?" name="selectPrat" method="get">
    <select name="prat_id" onchange="Coeff.refreshList(this.value);">
      <option value="">&mdash; {{tr}}compta-none_prat{{/tr}}</option>
      {{mb_include module=mediusers template=inc_options_mediuser selected=$prat->_id list=$listPrat}}
    </select>
  </form>

  {{if !$prat->_id}}
    <div class="small-info">{{tr}}CMediusers-select-praticien{{/tr}}</div>
  {{else}}
    <table class="tbl">
      <tr>
        <th class="title" colspan="4">{{tr}}CFactureCoeff.accord{{/tr}} {{tr}}from{{/tr}} {{$prat->_view}}</th>
      </tr>
      <tr>
        <th class="category narrow">{{tr}}Action{{/tr}}</th>
        <th class="category">{{mb_title class=CFactureCoeff field=nom}}</th>
        <th class="category">{{mb_title class=CFactureCoeff field=coeff}}</th>
        <th class="category">{{mb_title class=CFactureCoeff field=description}}</th>
      </tr>
      {{foreach from=$coeffs item=_coeff}}
        <tr>
          <td>
            <button type="button" class="edit notext" onclick="Coeff.edit('{{$_coeff->_id}}');">
              {{tr}}CFactureCoeff-title-modify{{/tr}}
            </button>
          </td>
          <td>{{mb_value object=$_coeff field=nom}}</td>
          <td>{{mb_value object=$_coeff field=coeff}}</td>
          <td>{{mb_value object=$_coeff field=description}}</td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="4" class="empty">{{tr}}CFactureCoeff.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  {{/if}}
</div>