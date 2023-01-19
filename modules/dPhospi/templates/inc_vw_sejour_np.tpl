{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $curr_sejour->_id != ""}}
  <tr class="{{$curr_sejour->type}} {{if $object->_id == $curr_sejour->_id}}selected{{/if}}">
    <td style="padding: 0;">
      <button class="lookup notext" style="margin: 0;" onclick="popEtatSejour({{$curr_sejour->_id}});">Etat du séjour</button>
    </td>

    <td>
      {{assign var=sejour_nda value=$curr_sejour->_NDA}}
      <a class="text" href="#1"
         onclick="markAsSelected(this); addSejourIdToSession('{{$curr_sejour->_id}}'); loadViewSejour({{$curr_sejour->_id}},'{{$date}}');{{if "oxLaboClient"|module_active && "oxLaboClient alert_result_critical modal_alert_result_critical"|gconf && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}OxLaboClient.showModaleCriticalResult('{{$curr_sejour->_id}}'){{/if}}">
      <span class="CPatient-view {{if !$curr_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $curr_sejour->septique}}septique{{/if}}"
            onmouseover="ObjectTooltip.createEx(this, '{{$curr_sejour->_guid}}');">
        {{$curr_sejour->_ref_patient}}
          {{if $curr_sejour->_ref_patient->_homonyme}}
              {{mb_include module=patients template=patient_state/inc_flag_homonyme}}
          {{/if}}
      </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$curr_sejour->_ref_patient}}

        {{mb_include module=planningOp template=inc_icon_autorisation_permission sejour=$curr_sejour curr_affectation=null}}
      </a>
    </td>

    <td></td>

    <td style="padding: 1px;">
      <div class="imeds_alert"
           onclick="markAsSelected(this); addSejourIdToSession('{{$curr_sejour->_id}}'); loadViewSejour('{{$curr_sejour->_id}}', '{{$date}}'); tab_sejour.setActiveTab('Imeds')">
        {{if $isImedsInstalled}}
          {{mb_include module=Imeds template=inc_sejour_labo sejour=$curr_sejour link="#"}}
        {{/if}}
      </div>
      {{if array_key_exists($sejour_nda, $labo_alert_by_nda)  && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
        <span id="OxLaboAlert_{{$sejour_nda}}">
          {{mb_include module=oxLaboClient template=vw_alerts object=$curr_sejour object_id=$curr_sejour->_id object_class=$curr_sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$labo_alert_by_nda.$sejour_nda.total alerts=$labo_alert_by_nda.$sejour_nda}}
        </span>
      {{/if}}
      {{if array_key_exists($sejour_nda, $new_labo_alert_by_nda) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
        <span id="OxLaboNewAlert_{{$sejour_nda}}">
          {{mb_include module=oxLaboClient template=vw_alerts object=$curr_sejour object_id=$curr_sejour->_id object_class=$curr_sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$new_labo_alert_by_nda.$sejour_nda|@count alerts=$new_labo_alert_by_nda.$sejour_nda alert_new_result=true}}
        </span>
      {{/if}}
      {{if "nouveal"|module_active && "nouveal general active_prm"|gconf}}
        {{assign var=sejour_id value=$curr_sejour->_id}}
        <span style="float: right;">
        {{mb_include module=nouveal template=inc_etat_patient etat_patient=$etats_patient.$sejour_id}}
      </span>
      {{/if}}
      {{mb_include module=dPfiles template=inc_icon_category_check object=$curr_sejour}}
    </td>

    <td class="action" style="padding: 1px;">
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_sejour->_ref_praticien initials=border}}
    </td>
  </tr>
{{/if}}
