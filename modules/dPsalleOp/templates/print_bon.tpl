{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient       value=$sejour->_ref_patient}}
{{assign var=etablissement value=$sejour->_ref_group}}

<html>
  <body>
    <table style="width: 100%;">
      <tr>
        <td style="width: 70%;">
          <strong>{{$etablissement->text}}</strong>
          {{if $etablissement->tel}} &ndash; Tel: {{mb_value object=$etablissement field=tel}}{{/if}}
          {{if $etablissement->fax}} &ndash; Fax: {{mb_value object=$etablissement field=fax}}{{/if}}
          <hr />
          <div>
            <strong>{{$patient->_view}}</strong>
            Né(e) le {{mb_value object=$patient field=naissance}} - ({{$patient->_age}}) - ({{$patient->_poids}} kg)
            <br />
            {{tr}}dPplanningOp-COperation of{{/tr}} {{$operation->_ref_plageop->date|date_format:$conf.date}}
            <strong>(I{{if $operation->_compteur_jour >=0}}+{{/if}}{{$operation->_compteur_jour}})</strong> - côté {{$operation->cote}}<br /><br />
            <strong>{{$operation->libelle}}</strong>

            <div style="text-align: left">
              {{foreach from=$operation->_ext_codes_ccam item=curr_ext_code}}
                <strong>{{$curr_ext_code->code}}</strong> :
                {{$curr_ext_code->libelleLong}}<br />
              {{/foreach}}
            </div>
          </div>

          {{if isset($antecedents.alle|smarty:nodefaults)}}
            {{assign var=allergies value=$antecedents.alle}}
            {{if $allergies|@count}}
              <strong>Allergies</strong>:
              {{foreach from=$allergies item=allergie name="allergies"}}
                {{if $allergie->date}}
                  {{$allergie->date|date_format:$conf.date}}:
                {{/if}}
                {{$allergie->rques}}
                {{if !$smarty.foreach.allergies.last}},{{/if}}
              {{/foreach}}
            {{/if}}
          {{/if}}
        </td>
        <td style="vertical-align: top;">
          {{foreach from=$sejour->_ref_affectations item=_affectation}}
            {{if $sejour->_ref_affectations|@count > 1}}
              du {{$_affectation->entree|date_format:$conf.date}} au {{$_affectation->sortie|date_format:$conf.date}}
            {{/if}}
            <strong>{{$_affectation}}</strong>
            <br />
          {{/foreach}}
          DE: {{$sejour->entree|date_format:$conf.date}}<br />
          DS: {{$sejour->sortie|date_format:$conf.date}}<br />
          {{if $sejour->_NDA}}
            Séjour [{{$sejour->_NDA}}]<br/>
          {{/if}}
          {{if $patient->_IPP}}
            IPP [{{$patient->_IPP}}]
          {{/if}}
        </td>
      </tr>
      <tr>
        <td colspan="2">
          {{if $praticien->_rpps_base64}}
            <br /> RPPS : <img src="{{$praticien->_rpps_base64}}" width="160" height="45"/>
          {{/if}}
          {{if $praticien->_adeli_base64}}
            ADELI : <img src="{{$praticien->_adeli_base64}}" width="160" height="45"/>
          {{/if}}
          {{if $praticien->_ref_signature->_data_uri}}
            Signature : <img src="{{$praticien->_ref_signature->_data_uri}}" width="160" height="45"/>
          {{/if}}
        </td>
      </tr>
    </table>

    <hr />

    <div>
      {{mb_label object=$operation field=flacons_$type}} : {{mb_value object=$operation field=flacons_$type}}

      <br /><br />

      {{mb_label object=$operation field=labo_$type}} : {{mb_value object=$operation field=labo_$type}}

      <br /><br />

      {{assign var=field value="description_$type"}}
      {{mb_label object=$operation field=description_$type}} : {{$operation->$field}}
    </div>
  </body>
</html>