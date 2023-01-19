{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $curr_consult->premiere}}
  {{assign var=consult_background value="background-color:#faa;"}}
{{elseif $curr_consult->derniere}}
  {{assign var=consult_background value="background-color:#faf;"}}
{{else}}
  {{assign var=consult_background value=""}}
{{/if}}

{{if $categorie->_id}}
  <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} style="{{$consult_background}}">
    {{mb_include module=cabinet template=inc_icone_categorie_consult
      consultation=$curr_consult
      categorie=$categorie
    }}
  </td>
{{/if}}
    
{{if $curr_consult->patient_id}}
  {{assign var=patient value=$curr_consult->_ref_patient}}
  <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} style="{{$consult_background}}" class="text">
    {{mb_ditto name=nom_patient value=$patient center=true}}
    {{if $filter->_print_ipp && $patient->_IPP}}
      [{{$patient->_IPP}}]
    {{/if}}
    {{if !$filter->_coordonnees && $curr_consult->visite_domicile}}
      {{assign var=adresse_patient value="`$patient->adresse` \n `$patient->cp` `$patient->ville`"}}
      {{if $patient->adresse || $patient->cp || $patient->ville}}
        <br/>{{mb_ditto name=adresse_patient value=$adresse_patient|nl2br center=true}}
      {{/if}}
    {{/if}}
  </td>

  {{if $filter->_coordonnees}}
    <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} class="text" style="{{$consult_background}}">
      {{assign var=adresse_patient value="`$patient->adresse` \n `$patient->cp` `$patient->ville`"}}
      {{if $patient->adresse || $patient->cp || $patient->ville}}
        {{mb_ditto name=adresse_patient value=$adresse_patient|nl2br center=true}}
      {{/if}}
    </td>
    <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} style="{{$consult_background}}">
      {{if $patient->tel}}
        {{mb_ditto name=tel_patient value=$patient->getFormattedValue('tel') center=true}}
      {{/if}}
      {{if $patient->tel2}}
        <br />{{mb_ditto name=tel2_patient value=$patient->getFormattedValue('tel2') center=true}}
      {{/if}}
    </td>
  {{elseif $filter->_telephone}}
    <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} style="{{$consult_background}}">
      {{if $patient->tel}}
        {{mb_ditto name=tel_patient value=$patient->getFormattedValue('tel') center=true}}
      {{/if}}
      {{if $patient->tel2}}
        <br />{{mb_ditto name=tel2_patient value=$patient->getFormattedValue('tel2') center=true}}
      {{/if}}
    </td>
  {{/if}}
  <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} class="me-text-align-left" style="text-align: center; {{$consult_background}}">
    {{assign var=age_patient value=$patient->_age}}
    {{if $patient->_annees != "??"}}
      {{assign var=naissance_formatted value=$patient->getFormattedValue('naissance')}}
      {{assign var=age_patient value="$age_patient \n ($naissance_formatted)"}}
    {{/if}}

    {{mb_ditto name=age_patient value=$age_patient|nl2br}}
  </td>
  {{if $show_lit}}
    <td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} style="{{$consult_background}}">
      {{$patient->_ref_curr_affectation}}
    </td>
  {{/if}}
{{elseif $curr_consult->groupee && $curr_consult->no_patient}}
  <td colspan="{{math equation='x-5' x=$main_colspan}}" style="{{$consult_background}}">[{{tr}}CConsultation-MEETING{{/tr}}]</td>
{{else}}
  <td colspan="{{math equation='x-5' x=$main_colspan}}" style="{{$consult_background}}">
    [{{tr}}CConsultation-PAUSE{{/tr}}]
  </td>
{{/if}}

<td class="text" style="{{$consult_background}}">
  {{if $categorie->_id}}
    <div>
      {{mb_include module=cabinet template=inc_icone_categorie_consult
        consultation=$curr_consult
        categorie=$categorie
        display_name=true
      }}
    </div>
  {{/if}}
  {{mb_value object=$curr_consult field=motif}}
</td>

<td class="text" style="{{$consult_background}}">
  {{mb_value object=$curr_consult field=rques}}
</td>

<td {{if $consult_anesth->operation_id}}rowspan="2"{{/if}} style="{{$consult_background}}">
  {{$curr_consult->_duree}}min
</td>

{{if $consult_anesth->operation_id}}
  </tr>
  <tr>
    {{* Keep table row out of condition *}}
    <td colspan="2" class="text" style="{{$consult_background}}">
      <div style="border-left: 4px solid #aaa; padding-left: 5px;">
      {{assign var=operation value=$consult_anesth->_ref_operation}}
  
      {{tr}}dPplanningOp-COperation of{{/tr}} {{$operation->_datetime|date_format:$conf.date}}
      - Dr {{$operation->_ref_praticien->_view}}<br />
      {{if $operation->libelle}}
        <em>[{{$operation->libelle}}]</em>
        <br />
      {{/if}}
      <!--
      {{foreach from=$operation->_ext_codes_ccam item=curr_code}}
        {{if !$curr_code->_code7}}<strong>{{/if}}
        <small>{{$curr_code->code}} : {{$curr_code->libelleLong}}</small>
        {{if !$curr_code->_code7}}</strong>{{/if}}
        <br/>
      {{/foreach}}
      -->
      </div>
    </td>
{{/if}}