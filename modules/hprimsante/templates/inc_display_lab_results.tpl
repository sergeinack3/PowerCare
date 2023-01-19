{{*
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$message.segments key=_pindex item=_patient}}
  <fieldset>
    <legend>Patient</legend>
    <table class="form">
      <tr>
        <th class="narrow" style="font-weight: bold;">Nom</th>
        <td>
          {{$_patient.last_name}}
          {{if $_patient.birth_name != ''}}
            ({{$_patient.birth_name}})
          {{/if}}
          {{$_patient.first_name}}
        </td>
      </tr>
      <tr>
        <th class="narrow" style="font-weight: bold;">Date de naissance</th>
        <td>
          {{$_patient.birth_date|date_format:$conf.date}}
        </td>
      </tr>
      <tr>
        <th class="narrow" style="font-weight: bold;">Sexe</th>
        <td>
          {{$_patient.sex}}
        </td>
      </tr>
    </table>
  </fieldset>
  {{foreach from=$_patient.analysis key=_aindex item=_analysis}}
    <fieldset>
      <legend>
        {{if $_analysis.names|@count}}
          {{foreach from=$_analysis.names item=_name}}
            {{$_name}}
          {{/foreach}}
        {{elseif $_analysis.codes|@count}}
          {{foreach from=$_analysis.codes item=_code}}
            {{$_code}}
          {{/foreach}}
        {{else}}
          Analyse
        {{/if}}
      </legend>

      <table class="form">
        <tr>
          <th class="narrow" style="font-weight: bold;">Status</th>
          <td>
            <span class="circled" style="cursor: help; background-color: {{$_analysis.status.color}};" title="{{$_analysis.status.desc}}">
              {{$_analysis.status.code}}
            </span>
          </td>
        </tr>
        <tr>
          <th class="narrow" style="font-weight: bold;">Date prélèvement</th>
          <td>{{$_analysis.date_acts|date_format:$conf.datetime}}</td>
        </tr>
        <tr>
          <th class="narrow" style="font-weight: bold;">Prescripteur</th>
          <td>{{$_analysis.prescriptor_name}}</td>
        </tr>
        <tr>
          <th class="narrow" style="font-weight: bold;">Laboratoire</th>
          <td>{{$message.header.sender.name}}</td>
        </tr>
        {{if $_analysis.interpretor_last_name}}
          <tr>
          <th class="narrow" style="font-weight: bold;">Interprète</th>
          <td>
            {{$_analysis.interpretor_last_name}} {{$_analysis.interpretor_first_name}}
          </td>
        </tr>
        {{/if}}
        {{if $_analysis.assistant_last_name}}
          <tr>
          <th class="narrow" style="font-weight: bold;">Assistant</th>
          <td>
            {{$_analysis.assistant_last_name}} {{$_analysis.assistant_first_name}}
          </td>
        </tr>
        {{/if}}
        {{if $_analysis.technician_last_name}}
          <tr>
          <th class="narrow" style="font-weight: bold;">Technicien</th>
          <td>
            {{$_analysis.technician_last_name}} {{$_analysis.technician_first_name}}
          </td>
        </tr>
        {{/if}}
        {{if $_analysis.operator_last_name}}
          <tr>
          <th class="narrow" style="font-weight: bold;">Opérateur</th>
          <td>
            {{$_analysis.operator_last_name}} {{$_analysis.operator_first_name}}
          </td>
        </tr>
        {{/if}}
      </table>

      <table class="tbl">
        <tr>
          <th class="title" colspan="7">Résultats</th>
        </tr>
        <tr>
          <th class="category narrow">
            Status
          </th>
          <th class="category narrow">
            Analyse
          </th>
          <th class="category narrow">
            Résultat
          </th>
          <th class="category narrow">
            Unité
          </th>
          <th class="category narrow">
            Normales
          </th>
          <th class="category narrow">
            Anormalité
          </th>
          <th class="category narrow">
            Examinateur
          </th>
        </tr>
        {{foreach from=$_analysis.observations item=_observation}}
          <tr>
            <td class="narrow" style="text-align: center;">
              <span class="circled" style="cursor: help; background-color: {{$_observation.status.color}};" title="{{$_observation.status.desc}}">
                {{$_observation.status.name}}
              </span>
            </td>
            <td class="narrow" style="text-align: center;">
              {{$_observation.test_name}}
            </td>
            <td class="narrow" style="text-align: right;">
              {{if $_observation.type == 'AD'}}
                {{$_observation.result.street}} {{$_observation.result.comp}}<br/>
                {{$_observation.result.postal}} {{$_observation.result.city}}<br/>
                {{$_observation.result.country}}
              {{elseif $_observation.type == 'CE' || $_observation.type == 'CNA'}}
                {{$_observation.result.name}} ({{$_observation.result.code}})
              {{elseif $_observation.type == 'DT'}}
                {{$_observation.result|date_format:$conf.datetime}}
              {{elseif $_observation.type == 'PN'}}
                {{$_observation.result.title}} {{$_observation.result.last_name}} {{$_observation.result.first_name}}
              {{elseif $_observation.type == 'TX'}}
                <div class="text">{{$_observation.result}}</div>
              {{else}}
                {{$_observation.result}}
              {{/if}}
            </td>
            <td class="narrow" style="text-align: center;">{{$_observation.unit}}</td>
            <td class="narrow" style="text-align: center;">{{$_observation.normal}}</td>
            <td class="narrow" style="text-align: center;">
              {{if $_observation.abnormal|@count}}
                {{foreach from=$_observation.abnormal item=_abnormal}}
                  <span class="circled" style="cursor: help; background-color: {{$_abnormal.color}};" title="{{$_abnormal.desc}}">
                    {{$_abnormal.code}}
                  </span>
                {{/foreach}}
              {{/if}}
            </td>
            <td class="narrow" style="text-align: center;">
              {{mb_ditto name="examinator$_pindex$_aindex" value=$_observation.validator_name}}
            </td>
          </tr>
        {{/foreach}}
      </table>
    </fieldset>
  {{/foreach}}
{{/foreach}}