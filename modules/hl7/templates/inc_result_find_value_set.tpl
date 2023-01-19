{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $error}}
  <div class="small-error">SOAP Fault : {{$error}}</div>
  {{mb_return}}
{{/if}}

<table class="tbl main">
  <tr>
    <th class="narrow"> {{tr}}CHL7v3EventSVSValueSet-id{{/tr}} </th>
    <td> {{$value_set->id}} </td>
  </tr>
  <tr>
    <th> {{tr}}CHL7v3EventSVSValueSet-version{{/tr}} </th>
    <td> {{$value_set->version|date_format:$conf.date}} </td>
  </tr>
  <tr>
    <th> {{tr}}CHL7v3EventSVSValueSet-displayName{{/tr}} </th>
    <td> {{$value_set->displayName}} </td>
  </tr>

  {{foreach from=$value_set->concept_list item=_concept_list}}
    <tr>
      <td colspan="2">
        <fieldset>
          <legend> {{$_concept_list->lang}} </legend>

          <table class="tbl main">
            <tr>
              <th> {{tr}}CHL7v3EventSVSConcept-displayName{{/tr}} </th>
              <th> {{tr}}CHL7v3EventSVSConcept-codeSystem{{/tr}} </th>
              <th> {{tr}}CHL7v3EventSVSConcept-code{{/tr}} </th>
            </tr>
           {{foreach from=$_concept_list->concept item=_concept}}
            <tr>
              <td> {{$_concept->displayName}} </td>
              <td> {{$_concept->codeSystem}} </td>
              <td> {{$_concept->code}} </td>
            </tr>
            {{/foreach}}
            </table>
        </fieldset>
      </td>
    </tr>
  {{/foreach}}
</table>