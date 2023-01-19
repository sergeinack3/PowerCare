{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new" type="button" onclick="RegleEvt.editRegle(0);">
  {{tr}}CRegleAlertePatient-title-create{{/tr}}
</button>

<table class="main tbl">
  <tr>
    <th class="title" colspan="13">
      {{tr}}CRegleAlertePatient.all{{/tr}} ({{$current_group->_view}})
      <label style="float: right; font-size: 0.8em;">
        <input type="checkbox" name="show_canceled" onchange="$$('tr.hatching.regle').invoke('toggle');" />
        {{tr}}CPatient-action-Show canceled{{/tr}}
      </label>
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{mb_label class=CRegleAlertePatient field=function_id}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=name}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=age_operateur}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=age_valeur}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=sexe}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=programme_clinique_id}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=ald}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=diagnostics}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=pathologies}}</th>
    <th>{{tr}}CEvenementAlerteUser{{/tr}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=nb_anticipation}}</th>
    <th>{{mb_label class=CRegleAlertePatient field=periode_refractaire}}</th>
  </tr>
  {{foreach from=$regles item=_regle}}
    <tr {{if !$_regle->actif}}class="hatching regle" style="display: none;" {{/if}}>
      <td class="button">
        <button type="button" class="edit notext" onclick="RegleEvt.editRegle('{{$_regle->_id}}');"
                title="{{tr}}common-action-Edit{{/tr}}"
          {{if !$app->_ref_user->isAdmin() && $_regle->function_id && $app->_ref_user->function_id != $_regle->function_id}}
            disabled
          {{/if}}
        >
          {{tr}}common-action-Edit{{/tr}}
        </button>
      </td>
      <td>
        {{if $_regle->function_id}}
          <span onmouseover="ObjectTooltip.createEx(this, 'CFunctions-{{$_regle->function_id}}');">
            {{mb_value object=$_regle field=function_id}}
          </span>
        {{else}}
          -
        {{/if}}
      </td>
      <td>{{mb_value object=$_regle field=name}}</td>
      <td>{{mb_value object=$_regle field=age_operateur}}</td>
      <td>{{mb_value object=$_regle field=age_valeur}}</td>
      <td>{{mb_value object=$_regle field=sexe}}</td>
      <td>{{mb_value object=$_regle field=programme_clinique_id}}</td>
      <td>{{mb_value object=$_regle field=ald}}</td>
      <td>
        <ul>
          {{foreach from=$_regle->_ext_diagnostics item=_cim}}
            <li>{{$_cim->code}} - {{$_cim->libelle}}</li>
            {{foreachelse}}
            <li class="empty">{{tr}}None{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>
      <td>
        <ul>
            {{foreach from=$_regle->_ext_pathologies item=_cim}}
              <li>{{$_cim->code}} - {{$_cim->libelle}}</li>
                {{foreachelse}}
              <li class="empty">{{tr}}None{{/tr}}</li>
            {{/foreach}}
        </ul>
      </td>
      <td>
        <ul>
          {{foreach from=$_regle->_ref_users item=mediuser}}
            <li>{{mb_include module=mediusers template=inc_vw_mediuser}}</li>
            {{foreachelse}}
            <li class="empty">{{tr}}None{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>
      <td>{{mb_value object=$_regle field=nb_anticipation}}</td>
      <td>{{mb_value object=$_regle field=periode_refractaire}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="13" class="empty">{{tr}}CRegleAlertePatient.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
