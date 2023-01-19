{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry("cancelled-operations"));
</script>

<div class="me-padding-10">
  <ul class="control_tabs" id="cancelled-operations">
    {{foreach from=$counts item=_count key=_month}}
      <li><a href="#month-{{$_month}}">
          {{$_month}}
          <small>({{$_count}})</small>
        </a></li>
    {{/foreach}}
  </ul>
</div>

<div class="me-padding-10" style="height: 30px;">
  <span style="float: right">
    <form name="intervs" action="?" method="get" onsubmit="return checkForm(this)">
      <input type="hidden" name="m" value="stats" />
      <input type="hidden" name="tab" value="vw_cancelled_operations" />
      <select name="type_modif" onchange="this.form.submit()">
        <option value="annule" {{if $type_modif == "annule"}}selected="selected"{{/if}}>Interventions annulées le jour même</option>
        <option value="ajoute" {{if $type_modif == "ajoute"}}selected="selected"{{/if}}>Interventions ajoutées le jour même</option>
      </select>
      {{tr}}date.To_long{{/tr}} {{mb_field class=COperation field="_date_max" value=$date_max form="intervs" canNull="false" register=true onchange="this.form.submit()"}}
    </form>
  </span>
</div>

{{foreach from=$list item=month key=month_label}}
  <div id="month-{{$month_label}}" class="me-padding-10" style="display: none;">
    <table class="main tbl me-no-align">
      <tr>
        <th>{{mb_title class=COperation field=date}}</th>
        <th>{{mb_title class=COperation field=salle_id}}</th>
        <th>{{mb_title class=COperation field=chir_id}}</th>
        <th>{{mb_title class=CSejour field=patient_id}}</th>
        <th>{{mb_title class=CSejour field=type}}</th>
        <th>{{mb_title class=COperation field=libelle}}</th>
        <th>{{mb_title class=COperation field=rques}}</th>
        <th>{{mb_title class=COperation field=codes_ccam}}</th>
      </tr>

      {{foreach from=$month key=plage_status item=_operations}}
        <tr>
          <th colspan="100" class="section">{{tr}}COperation-title-{{$plage_status}}{{/tr}}</th>
        </tr>
        {{foreach from=$_operations item=op}}
          <tr>
            <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$op->_guid}}')">
              {{mb_value object=$op field=_datetime}}
            </span>
            </td>
            <td class="text">{{mb_value object=$op field=salle_id tooltip=true}}</td>
            <td>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$op->_ref_praticien}}
            </td>
            <td class="text">{{mb_value object=$op->_ref_sejour field=patient_id}}</td>
            <td>{{mb_value object=$op->_ref_sejour field=type}}</td>
            <td class="text">{{mb_value object=$op field=libelle}}</td>
            <td class="text compact">{{mb_value object=$op field=rques}}</td>
            <td>
              {{foreach from=$op->_codes_ccam item=_code}}
                {{$_code}}
                {{foreachelse}}
                <div class="empty">{{tr}}CActeCCAM.none{{/tr}}</div>
              {{/foreach}}
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="100" class="empty">{{tr}}COperation.none{{/tr}}</td>
          </tr>
        {{/foreach}}

        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}COperation.none{{/tr}}</td>
        </tr>
      {{/foreach}}

    </table>
  </div>
{{/foreach}}