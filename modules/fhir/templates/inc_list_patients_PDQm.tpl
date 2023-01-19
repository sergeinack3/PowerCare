{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="section" colspan="100">{{$total}} résultats</th>
  </tr>
  
  <tr>
    <th colspan="100">
      {{foreach from='Ox\Interop\Fhir\Profiles\CFHIR'|static:relation_map item=_icon key=_relation}}
        {{if $links && array_key_exists($_relation,$links)}}
          <button class="fa fa-{{$_icon}}" onclick="TestFHIR.requestWithURI('{{$links.$_relation}}', '{{$search_type}}')">
            {{$_relation}}
          </button>
        {{/if}}
      {{/foreach}}
    </th>
  </tr>

  <tr>
    <th class="narrow"></th>
    <th class="narrow">#</th>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th class="narrow">{{tr}}CPatient-naissance-court{{/tr}}</th>
    <th>{{tr}}CPatient-sexe{{/tr}}</th>
    <th>{{tr}}CPatient-adresse{{/tr}}</th>
    <th>Identifiers</th>
  </tr>

  {{foreach from=$results item=_patient}}
    <tr>
      <td>
        {{if !$id}}
          <button class="search notext compact" onclick="TestFHIR.readPDQm('{{$_patient->_fhir_resource_id}}', '{{$format}}')"
                  title="Afficher le dossier complet">
            {{tr}}Show{{/tr}}
          </button>
        {{/if}}
      </td>
      <td>
        {{$_patient->_fhir_resource_id}}
      </td>
      <td>
        <div class="text noted">
          {{mb_value object=$_patient field="_view"}}
        </div>
      </td>
      <td>
        {{mb_value object=$_patient field="naissance"}}
      </td>
      <td>
        {{mb_value object=$_patient field="sexe"}}
      </td>
      <td class="text compact">
        <span style="white-space: nowrap;">{{$_patient->adresse|spancate:30}}</span>
        <span style="white-space: nowrap;">{{$_patient->cp}} {{$_patient->ville|spancate:20}}</span>
      </td>
      <td>
        <ul>
          {{foreach from=$_patient->_identifiers item=_identifier}}
            <li>{{$_identifier.system}} | <strong>{{$_identifier.value}}</strong></li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">{{tr}}dPpatients-CPatient-no-exact-results{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
