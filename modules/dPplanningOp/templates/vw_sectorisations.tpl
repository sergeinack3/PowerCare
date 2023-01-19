{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=sectorisation ajax=1}}
{{if !$refresh_mode}}
<div id="sectorisation_container">
{{/if}}
  {{if !$conf.dPplanningOp.CRegleSectorisation.use_sectorisation}}
    <div class="small-warning">
      {{tr}}CRegleSectorisation-msg-not-active{{/tr}}
    </div>
  {{/if}}

  <button class="new me-primary" onclick="Sectorisation.edit(0)">{{tr}}CRegleSectorisation-title-create{{/tr}}</button>

  <input type="checkbox" {{if $show_inactive}}checked{{/if}} name="_show_caduc" id="showInactive_show_caduc"
         onchange="Sectorisation.refreshList(this.checked ? 1 : 0);" />
  <label for="showInactive_show_caduc">{{tr}}CRegleSectorisation-show-inactive{{/tr}}</label>

  <table class="tbl">
    <tr>
      <th class="narrow">{{tr}}Actions{{/tr}}</th>
      <th class="narrow">{{tr}}CRegleSectorisation-priority{{/tr}}</th>
      <th>{{tr}}CFunctions{{/tr}}</th>
      <th>{{tr}}CMediusers{{/tr}}</th>
      <th>{{tr}}CRegleSectorisation-duree_min{{/tr}}</th>
      <th>{{tr}}CRegleSectorisation-duree_max{{/tr}}</th>
      <th>{{tr}}CRegleSectorisation-date_min{{/tr}}</th>
      <th>{{tr}}CRegleSectorisation-date_max{{/tr}}</th>
      <th>{{tr}}CRegleSectorisation-type_admission{{/tr}}</th>
      <th>{{tr}}CRegleSectorisation-type_pec{{/tr}}</th>
      <th>{{mb_title class=CRegleSectorisation field=age_min}}</th>
      <th>{{mb_title class=CRegleSectorisation field=age_max}}</th>
      <th>{{mb_title class=CRegleSectorisation field=handicap}}</th>
      <th>Direction</th>
    </tr>

    {{foreach from=$regles item=_regle}}
      <tr {{if $_regle->_inactive}}class="hatching"{{/if}}>
        <td>
          <button class="edit notext" onclick="Sectorisation.edit('{{$_regle->_id}}')">{{tr}}Edit{{/tr}}</button>
          <button class="duplicate notext" onclick="Sectorisation.clone('{{$_regle->_id}}')">{{tr}}Duplicate{{/tr}}</button>
        </td>

        <td style="text-align: center"><strong>{{mb_value object=$_regle field=priority}}</strong></td>
        <td>{{mb_include module="mediusers" template="inc_vw_function" function=$_regle->_ref_function}}</td>
        <td>{{mb_include module="mediusers" template="inc_vw_mediuser" mediuser=$_regle->_ref_praticien}}</td>
        <td>{{if $_regle->duree_min}}{{mb_value object=$_regle field=duree_min}} {{tr}}night{{/tr}}(s){{/if}}</td>
        <td>{{if $_regle->duree_max}}{{mb_value object=$_regle field=duree_max}} {{tr}}night{{/tr}}(s){{/if}}</td>
        <td>{{mb_value object=$_regle field=date_min}}</td>
        <td>{{mb_value object=$_regle field=date_max}}</td>
        <td>{{if $_regle->type_admission}}{{tr}}CSejour._type_admission.{{$_regle->type_admission}}{{/tr}}{{/if}}</td>
        <td>{{if $_regle->type_pec}}{{tr}}CSejour.type_pec.{{$_regle->type_pec}}{{/tr}}{{/if}}</td>
        <td style="text-align: center">{{mb_value object=$_regle field=age_min}}</td>
        <td style="text-align: center">{{mb_value object=$_regle field=age_max}}</td>
        <td style="text-align: center">{{if $_regle->handicap}}{{mb_value object=$_regle field=handicap}}{{/if}}</td>

        <td><strong>>> {{$_regle->_ref_service}}</strong></td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="14">{{tr}}CRegleSectorisation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{if !$refresh_mode}}
</div>
{{/if}}