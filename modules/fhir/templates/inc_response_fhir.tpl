{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=fhir_uid}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-response-fhir-{{$fhir_uid}}'));
</script>

{{if $id}}
  <h3 style="text-align: center;">Patient with ID <code style=" font-weight: bold;">{{$id}}</code></h3>
{{/if}}

<pre>{{$query.event}}?{{$query.data|urldecode}}</pre>

<ul id="tabs-response-fhir-{{$fhir_uid}}" class="control_tabs small">
  <li><a href="#results-{{$fhir_uid}}">{{tr}}CFHIR-message-Results{{/tr}}</a></li>
  <li><a href="#response-{{$fhir_uid}}">{{tr}}CFHIR-message-Response{{/tr}}</a></li>
</ul>

<div id="results-{{$fhir_uid}}" style="display: none;">
  {{if $search_type == "CPDQm"}}
    {{mb_include module="fhir" template="inc_list_patients_PDQm"}}
  {{elseif $search_type == "CMHD"}}
    {{mb_include module="fhir" template="inc_list_result_MHD"}}
  {{else}}
    {{mb_include module="fhir" template="inc_list_result_PIXm"}}
  {{/if}}
</div>

<div id="response-{{$fhir_uid}}" style="display: none;">
  <pre>HTTP {{$response_code}} {{$response_message}}</pre>

  {{if $search_type != "CMHD"}}
    {{$response|highlight:$lang}}
  {{else}}
    {{$response}}
  {{/if}}
</div>