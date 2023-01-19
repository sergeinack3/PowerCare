{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.setTabCount('rhs-no-charge-{{$rhs_date_monday}}', '{{$count_sej_rhs_no_charge}}');
    Charged.refresh('{{$rhs_date_monday}}');
  });
</script>

{{assign var=days value='Ox\Mediboard\Ssr\CRHS'|static:days}}

<form class="prepared" name="editRHS-{{$rhs_date_monday}}" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete: Sejour.refresh.curry('{{$rhs_date_monday}}')})">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="dosql" value="do_facture_rhss_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="facture" value="1" />
  <input type="hidden" name="date_monday" value="{{$rhs_date_monday}}" />
  
  <button type="button" class="print" onclick="CotationRHS.printRHS  (this.form)">{{tr}}Print{{/tr}}</button>
  <button type="button" class="tick"  onclick="CotationRHS.chargeRHS (this.form)">{{tr}}Charge{{/tr}}</button>
  <button type="button" class="cancel" onclick="CotationRHS.restoreRHS(this.form)">{{tr}}Restore{{/tr}}</button>

  <button type="button" name="filter_services" onclick="Sejour.selectServices('rhs-no-charge', '{{$rhs_date_monday}}');" class="search"
          style="float: left;">{{tr}}CService|pl{{/tr}}</button>

  <input type="checkbox" name="all_rhs" id="editRHS-all_rhs" value="1" />
  <label for="editRHS-all_rhs">{{tr}}CRHS-include_after{{/tr}}</label>

  <div style="float: right;">
    <label style="visibility: hidden;" class="rhs-charged" title="{{tr}}CRHS-with_closed-title{{/tr}}">
      <input type="checkbox" checked="checked" onchange="Charged.toggle(this);Charged.countLinesChecked('{{$rhs_date_monday}}');" name="rhs_facture"/>
      {{tr}}Hide{{/tr}} <span>0</span> {{tr}}CRHS-with_closed{{/tr}}
    </label>
  </div>

  <table class="tbl">
    <tr>
      <th class="narrow">
        <input name="check_lines_rhs" type="checkbox" onchange="Charged.addSome('{{$rhs_date_monday}}', this.checked ? 1 : 0);"/>
      </th>
      <th>{{mb_title class=CSejour field=patient_id}}</th>
      <th>{{mb_title class=CSejour field=entree}}</th>
      <th>{{mb_title class=CSejour field=sortie}}</th>
      <th>{{mb_title class=CSejour field=service_id}}</th>
    </tr>
    {{foreach from=$sejours_rhs item=_rhs}}
      {{assign var=arretee value=$_rhs->facture|ternary:"arretee":""}}
      <tr {{if $_rhs->facture}} class="charged" style="display: none"{{/if}}>
        {{assign var=_sejour value=$_rhs->_ref_sejour}}
        <td class="{{$arretee}}">
          <input type="checkbox" class="rhs" name="sejour_ids[{{$_sejour->_id}}]" value="{{$_sejour->_id}}" onchange="Charged.countLinesChecked('{{$rhs_date_monday}}');"/>
         </td>

        <td class="{{$arretee}} text">
          {{mb_include module=system template=inc_object_notes object=$_rhs float=right}}
          {{mb_include module=ssr template=inc_view_patient patient=$_sejour->_ref_patient}}
        </td>

        <td class="{{$arretee}}">
          {{mb_value object=$_sejour field=entree format=$conf.date}}
        </td>
        
        <td class="{{$arretee}}">
          {{mb_value object=$_sejour field=sortie format=$conf.date}}
        </td>
        
        <td class="{{$arretee}}" style="text-align: center;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
           {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}}
          </span>
          <div class="opacity-60">
           {{mb_value object=$_sejour field=service_id}}
          </div>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CSejour.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>