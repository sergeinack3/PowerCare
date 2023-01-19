{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new me-primary" type="button"
        onclick="ParametrageMode.editCharge(0)">{{tr}}CChargePriceIndicator-title-create{{/tr}}</button>
<button class="hslip" type="button"
        onclick="ParametrageMode.importModeTraitement()">{{tr}}CChargePriceIndicator-import{{/tr}}</button>
<button class="hslip" type="button"
        onclick="ParametrageMode.exportModeTraitement()">{{tr}}CChargePriceIndicator-export{{/tr}}</button>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CChargePriceIndicator field=code}}</th>
    <th class="narrow">{{mb_title class=CChargePriceIndicator field=color}}</th>
    <th>{{mb_title class=CChargePriceIndicator field=libelle}}</th>
    <th>{{mb_title class=CChargePriceIndicator field=type}}</th>
    <th>{{mb_title class=CChargePriceIndicator field=type_pec}}</th>
    <th class="narrow">{{mb_title class=CChargePriceIndicator field=actif}}</th>
  </tr>
  {{foreach from=$list_cpi item=_cpi}}
    <tr>
      <td>
        <button type="button" class="edit notext me-tertiary" onclick="ParametrageMode.editCharge({{$_cpi->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
        {{mb_value object=$_cpi field=code}}
      </td>
      <td style="background-color: #{{$_cpi->color}}; color:#{{$_cpi->_font_color}};"><tt>{{$_cpi->color}}</tt></td>
      <td>{{mb_value object=$_cpi field=libelle}}</td>
      <td>{{mb_value object=$_cpi field=type}}</td>
      <td>{{mb_value object=$_cpi field=type_pec}}</td>
      <td>
        <form name="editActif{{$_cpi->_guid}}"  method="post" onsubmit="return onSubmitFormAjax(this)">
          {{mb_key object=$_cpi}}
          {{mb_class object=$_cpi}}
          {{mb_field object=$_cpi field="actif" onchange=this.form.onsubmit()}}
        </form>
        <span></span>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CChargePriceIndicator.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
