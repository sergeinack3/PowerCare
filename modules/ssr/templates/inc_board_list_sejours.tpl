{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !"dPprescription"|module_active && $mode == "plannable"}}
  <div class="small-warning">
    <div>{{tr}}ssr-param_prescription_no_acces{{/tr}}</div>
    <div>{{tr}}ssr-prescription_reeduc_no_acces{{/tr}}</div>
  </div>
  {{mb_return}}
{{/if}}

<script>
Main.add(function() {
  Control.Tabs.setTabCount.curry('board-sejours-{{$mode}}', '{{$sejours|@count}}');
})
</script>
<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th colspan="3">
      {{mb_title class=CSejour field=patient_id}} /
      {{mb_title class=CPatient field=_age}}
    </th>
    <th>{{mb_title class=CSejour field=libelle}}</th>
    <th class="narrow">{{mb_title class=CSejour field=entree}}</th>
    <th class="narrow">{{mb_title class=CSejour field=sortie}}</th>
    <th class="narrow" colspan="2">
      <label title="{{tr}}CEvenementSSR-title_cell{{/tr}}">{{tr}}CEvenementSSR-court{{/tr}}</label>
    </th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    {{assign var=bilan value=$_sejour->_ref_bilan_ssr}}
    <tr {{if $_sejour->_count_evenements_ssr_week}} style="font-weight: bold;" {{/if}}>
      <td class="text {{if !$bilan->_encours}}arretee{{/if}}">
        {{assign var=prescription value=$_sejour->_ref_prescription_sejour}}
        <a href="?m={{$m}}&tab=vw_aed_sejour_ssr&sejour_id={{$_sejour->_id}}#planification">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
            {{mb_value object=$_sejour field=patient_id}}
          </span>

          {{mb_include module=patients template=inc_icon_bmr_bhre}}
        </a>
      </td>
      <td class="narrow" style="position: relative;">
        {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
          {{assign var=prescription_id value=$prescription->_id}}
          {{mb_include module=system template=inc_icon_alerts object=$prescription callback="function() {BoardSejours.updateTab('$mode');}" nb_alerts=$prescription->_count_alertes}}
        {{else}}
          {{if $prescription->_count_fast_recent_modif}}
            <div class="me-bulb-info me-bulb-ampoule" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')">
              <img src="images/icons/ampoule.png" class="me-no-display"/>
            </div>
            {{mb_include module=system template=inc_vw_counter_tip count=$prescription->_count_fast_recent_modif}}
          {{/if}}
        {{/if}}
      </td>
      <td class="narrow">
        {{mb_value object=$patient field=_age}}
      </td>
      <td class="text">
        {{if $_sejour->hospit_de_jour && $bilan->_demi_journees}}
          <img style="float: right;" title="{{mb_value object=$bilan field=_demi_journees}}" src="modules/ssr/images/dj-{{$bilan->_demi_journees}}.png" />
        {{/if}}
        {{mb_value object=$_sejour field=libelle}}
      </td>
      <td>{{mb_value object=$_sejour field=entree format=$conf.date}}</td>
      <td>{{mb_value object=$_sejour field=sortie format=$conf.date}}</td>

      <td style="text-align: right;">
        {{assign var=count_evenements value=$_sejour->_count_evenements_ssr_week}}
        {{$count_evenements|nozero}}
      </td>

      <td style="text-align: right;">
        {{assign var=count_evenements value=$_sejour->_count_evenements_ssr}}
        {{$count_evenements|nozero}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
