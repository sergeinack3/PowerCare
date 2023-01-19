{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=onclick         value=null}}
{{mb_default var=id              value=null}}
{{mb_default var=title           value=$categorie->nom_categorie}}
{{mb_default var=alt             value=$categorie->nom_categorie}}
{{mb_default var=display_name    value=false}}
{{mb_default var=patient         value=null}}
{{mb_default var=consultation    value=null}}
{{mb_default var=counter         value=0}}
{{mb_default var=counter_consult value=0}}

{{if $consultation && $consultation->_id}}
  {{mb_default var=patient     value=$consultation->_ref_patient}}
  {{assign var=consultation_id value=$consultation->_id}}
    {{if array_key_exists($consultation_id, $categorie->_meeting_order)}}
        {{assign var=counter value=$categorie->_meeting_order.$consultation_id}}
    {{/if}}
    {{if $patient && $patient->_id}}
      {{assign var=counter_consult value=$categorie->countRefConsultations($patient->_id)}}
    {{/if}}
{{elseif $categorie->seance && $patient}}
  {{assign var=counter         value=$categorie->countRefConsultations($patient->_id)}}
  {{assign var=counter_consult value=$categorie->countRefConsultations($patient->_id)}}
{{/if}}

<span {{if $onclick}} onclick="{{$onclick}}" {{/if}}>
  <img style="cursor: pointer;"
     {{if $id}}id="{{$id}}" {{/if}}
     src="./modules/dPcabinet/images/categories/{{$categorie->nom_icone|basename}}"
     {{if $title}} title="{{$title}} {{if $categorie->seance && $patient}}({{tr var1=$counter_consult var2=$categorie->max_seances}}CConsultationCategorie-You have %s consultations of %s visits in this session group{{/tr}}){{/if}}" {{/if}}
     {{if $alt}}   alt="{{$alt}}" {{/if}}
  />
</span>
{{if $display_name}}
  {{$categorie|spancate}}
    {{if $categorie->seance && $patient}}
      <span id="nb_groupe_seance" title="{{tr}}CConsultationCategorie-Number of consultation in this session group|pl{{/tr}}">
      ({{tr var1=$counter var2=$categorie->max_seances}}CConsultationCategorie-Session %s of %s{{/tr}})
      </span>
    {{/if}}
    {{if $categorie->seance && $categorie->_threshold_alert}}
      {{if $counter >= $categorie->max_seances}}
        <i class="fas fa-exclamation-triangle" style="color: #ff574d; cursor: pointer;"
           title="{{tr}}CConsultationCategorie-msg-Maximum number of sessions reached for this patient{{/tr}}"
           onclick="Consultation.checkSessionThreshold('{{$consultation_id}}');"></i>
      {{elseif $counter > $categorie->_threshold_alert}}
        <i class="fas fa-exclamation-triangle" style="color: orange; cursor: pointer;"
           title="{{tr var1=$categorie->max_seances}}CConsultationCategorie-msg-Be careful, you will soon reach the maximum number of sessions (%s){{/tr}}"
           onclick="Consultation.checkSessionThreshold('{{$consultation_id}}');"></i>
      {{/if}}
    {{/if}}
{{/if}}
