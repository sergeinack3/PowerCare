{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=antecedent ajax=true}}
{{mb_default var=name_span value="atcd_allergies"}}
{{mb_default var=show_atcd value=1}}
{{mb_default var=sejour_id value=0}}
{{mb_default var=ajax value=true}}

{{unique_id var=unique_atcd}}
{{if $ajax}}
  <script>
    Main.add(function () {
      Antecedent.loadAntecedents('{{$patient->_id}}', '{{$show_atcd}}', '{{$sejour_id}}', '{{$unique_atcd}}');
    });
  </script>
{{/if}}
<span id="{{$name_span}}">
  <span id="id_antecedents_allergies_{{$patient->_id}}_{{$unique_atcd}}">
    {{if !$ajax}}
      {{if $dossier_medical->_id}}

        {{if $show_atcd && $dossier_medical->_count_antecedents}}
          {{assign var=dossier_medical_atcd value=$dossier_medical}}
          {{if $sejour->_ref_dossier_medical && $sejour->_ref_dossier_medical->_id}}
            {{assign var=dossier_medical_atcd value=$sejour->_ref_dossier_medical}}
          {{/if}}
          <span class="texticon texticon-atcd"
                onmouseover="ObjectTooltip.createEx(this, '{{$dossier_medical_atcd->_guid}}', 'antecedents');">{{tr}}CAntecedent.court{{/tr}}</span>
        {{/if}}

        {{if $dossier_medical->_count_allergies > 0}}
        <span class="texticon texticon-allergies-warning"
              onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}', 'allergies');">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>

{{elseif $dossier_medical->_ref_allergies|@count}}

        <span class="texticon texticon-allergies-ok"
              title="{{tr}}CAntecedent-No known allergy-desc{{/tr}}">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
      {{/if}}

      {{/if}}
    {{/if}}
  </span>
</span>