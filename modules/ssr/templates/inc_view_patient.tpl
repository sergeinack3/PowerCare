{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient_guid value=$patient->_guid}}

{{mb_default var=link   value="#$patient_guid"}}
{{mb_default var=onclick value=null}}
{{mb_default var=bebe_indicator value=false}}
{{mb_default var=ambu value=false}}

{{assign var=statut value="present"}}

{{if $_sejour->septique}}
  {{assign var=statut value="septique"}}
{{/if}}

{{if !$_sejour->entree_reelle || ($_sejour->_ref_prev_affectation && $_sejour->_ref_prev_affectation->_id && $_sejour->_ref_prev_affectation->effectue == 0)}}
  {{assign var=statut value="attente"}}
{{/if}}

{{if $_sejour->sortie_reelle || ($_sejour->_ref_curr_affectation && $_sejour->_ref_curr_affectation->effectue == 1 && !$ambu)}}
  {{assign var=statut value="sorti"}}
{{/if}}
<a href="{{$link}}" {{if $onclick}} onclick="{{$onclick}}" {{/if}}>
  <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient_guid}}')"
    class="{{if $statut == "attente"}}patient-not-arrived{{/if}} {{if $statut == "septique"}}septique{{/if}} {{if $statut == "sorti"}}me-hatching{{/if}}"
    style="{{if $statut == "sorti"}}background-image:url(images/icons/ray.gif); background-repeat:repeat;{{/if}} font-size: larger">

    {{if ($_sejour->_ref_curr_affectation && $_sejour->_ref_curr_affectation->parent_affectation_id)
        || ($_sejour->_ref_next_affectation && $_sejour->_ref_next_affectation->parent_affectation_id)}}
      <span style="font-size: 1.5em">&rarrhk;</span>
    {{/if}}

    <span class="CPatient-view">{{$patient}}</span>

    {{mb_include module=patients template=inc_icon_bmr_bhre}}
  </strong>
</a>

{{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
{{$patient->_age}}
