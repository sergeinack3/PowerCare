{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  {{if !$group_by_patient || $smarty.foreach.patients_list.first}}
  <td {{if $group_by_patient}}rowspan="{{$list_patient|@count}}" {{/if}}>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
          {{$_patient->_view}} ({{$_patient->sexe|strtoupper}})
        </span>
  </td>
  {{/if}}
    {{if !$group_by_patient || $smarty.foreach.patients_list.first}}
  <td class="text compact" {{if $group_by_patient}}rowspan="{{$list_patient|@count}}{{/if}}">
      {{if isset($_patient->_ref_antecedent|smarty:nodefaults)}}
          {{assign var=atcd value=$_patient->_ref_antecedent}}
        <strong>
            {{if $atcd->type == "alle"}}
              {{tr}}CAntecedent.appareil.alle{{/tr}} :
            {{else}}
              {{tr}}CAntecedent{{/tr}} :
            {{/if}}
        </strong>
        <br />
        <span onmouseover="ObjectTooltip.createEx(this, '{{$atcd->_guid}}')">
            {{$atcd}}
          </span>
      {{else}}
          {{if isset($_patient->_refs_antecedents|smarty:nodefaults) && $_patient->_refs_antecedents|@count}}
            <strong>
                {{tr}}CAntecedent.more{{/tr}} :
            </strong>
            <ul>
                {{foreach from=$_patient->_refs_antecedents item=_atcd}}
                    {{if $_atcd->type != "alle"}}
                      <li>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_atcd->_guid}}')">
                      {{$_atcd}}
                    </span>
                      </li>
                    {{/if}}
                {{/foreach}}
            </ul>
          {{/if}}
          {{if isset($_patient->_refs_allergies|smarty:nodefaults) && $_patient->_refs_allergies|@count}}
            <strong>
              {{tr}}CAntecedent-Allergie|pl{{/tr}} :
            </strong>
            <ul>
                {{foreach from=$_patient->_refs_allergies item=_allergie}}
                  <li>
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_allergie->_guid}}')">
                    {{$_allergie}}
                  </span>
                  </li>
                {{/foreach}}
            </ul>
          {{/if}}
          {{if isset($_patient->_ext_codes_cim|smarty:nodefaults) && $_patient->_ext_codes_cim|@count}}
            <strong>
              {{tr}}CCodeCIM10-diag|pl{{/tr}} :
            </strong>
            <ul>
                {{foreach from=$_patient->_ext_codes_cim item=_ext_code_cim}}
                  <li>
                      {{$_ext_code_cim->code}} : {{$_ext_code_cim->libelle}}
                  </li>
                {{/foreach}}
            </ul>
          {{/if}}

          {{* Pathologies and problems *}}
          {{if $_patient->_ref_dossier_medical->_ref_pathologies}}
              {{assign var=pathologies value=$_patient->_ref_dossier_medical->_ref_pathologies}}

              {{if 'Ox\Mediboard\Patients\CPathologie::amountPathologies'|static_call:$pathologies > 0}}
                <strong>{{tr}}CPathologie|pl{{/tr}} :</strong>
                <ul>
                    {{foreach from=$_patient->_ref_dossier_medical->_ref_pathologies item=_pathologie}}
                        {{if $_pathologie->type == "pathologie"}}
                          <li>{{$_pathologie->code_cim10}} : {{$_pathologie}}</li>
                        {{/if}}
                    {{/foreach}}
                </ul>
              {{/if}}

              {{if 'Ox\Mediboard\Patients\CPathologie::amountProblems'|static_call:$pathologies > 0}}
                <strong>{{tr}}CSearchCriteria-probleme_text{{/tr}} :</strong>
                <ul>
                    {{foreach from=$_patient->_ref_dossier_medical->_ref_pathologies item=_pathologie}}
                        {{if $_pathologie->type == "probleme"}}
                          <li>{{$_pathologie->code_cim10}} : {{$_pathologie}}</li>
                        {{/if}}
                    {{/foreach}}
                </ul>
              {{/if}}
          {{/if}}
      {{/if}}
      {{/if}}
  </td>
  <td class="me-text-align-center">
      {{if isset($_patient->_age_epoque|smarty:nodefaults)}}
          {{assign var=age_epoque value=$_patient->_age_epoque}}
          {{mb_ditto name=age value="$age_epoque ans"}}
      {{else}}
          {{mb_ditto name=age value=$_patient->_age}}
      {{/if}}
  </td>
  <td>
      {{if isset($_patient->_distant_object|smarty:nodefaults)}}
          {{assign var=object value=$_patient->_distant_object}}
          {{if $object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')">
                {{if $object->type_consultation == "suivi_patient"}}
                    {{tr var1=$object->_ref_plageconsult->date|date_format:$conf.date var2=$object->heure}}CConsultation-Monitoring patient of %s at %s{{/tr}}
                {{else}}
                    {{tr var1=$object->_ref_plageconsult->date|date_format:$conf.date var2=$object->heure}}CConsultation-Consultation of %s at %s{{/tr}}
                {{/if}}
            </span>
          {{elseif $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')">
              {{$object->_view}}
            </span>
            <br />
            &mdash;
            <strong>{{$object->_motif_complet}} </strong>
          {{else}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')">
              {{$object->_view}}
            </span>
            <br />
            &mdash;
            <strong>{{$object->libelle}} </strong>
          {{/if}}
      {{else}}
        &mdash;
      {{/if}}
  </td>

    {{if isset($_patient->_distant_line|smarty:nodefaults)}}
        {{assign var=line value=$_patient->_distant_line}}
      <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$line->_guid}}')">
            {{$line->_ucd_view}}
          </span>
      </td>
      <td class="text">
          {{$line->_ref_produit->_dci_view}}
      </td>
      <td>
          {{$line->_ref_produit->_ref_ATC_5_code}}
      </td>
      <td>
          {{$line->_ref_produit->_ref_ATC_5_libelle}}
      </td>
      <td>
          {{$line->commentaire}}
      </td>
    {{else}}
      <td colspan="5">&mdash;</td>
    {{/if}}
</tr>
