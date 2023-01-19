{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr class="clear">
    <th colspan="12">
      <h1>
        <a href="#" onclick="window.print()">
          {{tr var1=$date|date_format:$conf.longdate}}admissions-Permission of %s|pl{{/tr}} ({{$affectations|@count}}
          {{if $type_externe == "depart"}}
            {{tr}}admissions-Start(s){{/tr}}
          {{else}}
            {{tr}}admissions-Return(s){{/tr}}
          {{/if}})
        </a>
      </h1>
    </th>
  </tr>
  <tr>
    <th colspan="3"><strong>{{tr}}CPatient{{/tr}}</strong></th>
    <th rowspan="2">{{tr}}common-Practitioner{{/tr}}</th>
    <th rowspan="2">{{tr}}Hour{{/tr}}</th>
    {{if $type_externe == "depart"}}
      <th rowspan="2">{{tr}}CChambre{{/tr}}</th>
      <th rowspan="2">{{tr}}CSejour-destination{{/tr}}</th>
    {{else}}
      <th rowspan="2">{{tr}}CSejour-provenance{{/tr}}</th>
      <th rowspan="2">{{tr}}CChambre{{/tr}}</th>
    {{/if}}
    <th rowspan="2">{{tr}}CSejour-_duree{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CPatient-Last name / First name{{/tr}}</th>
    <th>{{tr}}CPatient-Birth (Age){{/tr}}</th>
    <th>{{tr}}CPatient-sexe{{/tr}}</th>
  </tr>
  {{foreach from=$affectations item=_aff}}
    {{assign var=_sejour value=$_aff->_ref_sejour}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    <tr>
      <td class="text">
        <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient}}
        </span>
      </td>

      <td>
        {{mb_value object=$patient field="naissance"}} ({{$patient->_age}})
      </td>

      <td>
        {{$patient->sexe}}
      </td>

      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
      </td>

      <td>
        <div style="float: right;">

        </div>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{$_aff->entree|date_format:$conf.time}}
        </span>
      </td>

      {{if $type_externe == "depart"}}
        <td class="text">
          {{$_aff->_ref_prev->_ref_lit->_view}}
        </td>
        <td class="text">
          {{$_aff->_ref_lit->_view}}
        </td>
      {{else}}
        <td class="text">
          {{$_aff->_ref_lit->_view}}
        </td>
        <td class="text">
          {{if $_aff->_ref_next->_id}}
            {{$_aff->_ref_next->_ref_lit->_view}}
          {{/if}}
        </td>
      {{/if}}

      <td class="text">
        {{tr var1=$_aff->_duree}}common-%d day(s){{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
