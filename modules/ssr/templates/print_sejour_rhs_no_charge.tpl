{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include style=$style template=open_printable}}

<div class="not-printable">
  <button type="button" class="print not-printable" onclick="window.print()">
    {{tr}}Print{{/tr}}
    {{$sejours_rhs|@count}} {{tr}}CRHS{{/tr}}
  </button>
</div>

{{assign var=days value='Ox\Mediboard\Ssr\CRHS'|static:days}}

{{foreach from=$sejours_rhs item=_rhs}}
{{assign var=sejour value=$_rhs->_ref_sejour}}
<table class="tbl">
  <tr>
    <th class="title" colspan="11">
      <big>
        {{$sejour}}<br/>
        {{tr}}CRHS{{/tr}} {{$_rhs}}
        &mdash;
        {{mb_include module=system template=inc_interval_date from=$_rhs->date_monday to=$_rhs->_date_sunday}}
      </big>
    </th>
  </tr>

  {{assign var=dependance value=$_rhs->_ref_dependances->_ref_dependances_rhs_bilan}}
  <tr>
    <th class="title" colspan="11">{{tr}}CDependancesRHS{{/tr}}</th>
  </tr>
  <tr>
    <th class="category">{{tr}}Category{{/tr}}</th>
    <th>{{mb_label object=$dependance field=habillage}}</th>
    <th>{{mb_label object=$dependance field=deplacement}}</th>
    <th>{{mb_label object=$dependance field=alimentation}}</th>
    <th>{{mb_label object=$dependance field=continence}}</th>
    <th>{{mb_label object=$dependance field=comportement}}</th>
    <th>{{mb_label object=$dependance field=relation}}</th>
  </tr>
  
  <tr>
    <th class="category">{{tr}}CDependancesRHS.degre{{/tr}}</th>
    <td>{{mb_value object=$dependance field=habillage}}</td>
    <td>{{mb_value object=$dependance field=deplacement}}</td>
    <td>{{mb_value object=$dependance field=alimentation}}</td>
    <td>{{mb_value object=$dependance field=continence}}</td>
    <td>{{mb_value object=$dependance field=comportement}}</td>
    <td>{{mb_value object=$dependance field=relation}}</td>
  </tr>
</table>

 <table class="tbl">
  <tr>
    <th class="title" colspan="11">{{tr}}mod-ssr-tab-ajax_totaux_rhs{{/tr}}</th>
  </tr>
  {{assign var=totaux value=$_rhs->_totaux}}
  {{foreach from=$_rhs->_ref_types_activite item=_type name=liste_types}}
    {{assign var=code value=$_type->code}}
    {{assign var=total value=$totaux.$code}}
    {{if $smarty.foreach.liste_types.index % 3 == 0}}
    <tr>
    {{/if}}
      <td class="button">
        {{if $total}}{{$total|default:'-'}}{{else}}-{{/if}}
      </td>
      <th style="text-align: left">{{$_type->_shortview}}</th>

    {{if $smarty.foreach.liste_types.last && $_rhs->_ref_types_activite|@count < 3}}
      <td colspan="{{math equation="(3-x)*2" x=$_rhs->_ref_types_activite|@count}}" style="width: {{math equation="(3-x)*31" x=$_rhs->_ref_types_activite|@count}}%"></td>
    {{/if}}
    {{if $smarty.foreach.liste_types.index % 3 == 3}}
    </tr>
    {{/if}}
  {{/foreach}}
</table>

  {{if "ssr general use_acte_presta"|gconf == 'csarr'}}
    {{mb_include module=ssr template=inc_lines_rhs rhs=$_rhs light_view=1 print=true}}
  {{elseif "ssr general use_acte_presta"|conf:"CGroups-$g" == 'presta'}}
    {{mb_include module=ssr template=inc_lines_rhs_acte_presta rhs=$_rhs}}
  {{/if}}

<br style="page-break-after: always;" />

{{foreachelse}}
<div class="small-info">{{tr}}CRHS-none{{/tr}}</div>
{{/foreach}}

{{mb_include style=$style template=close_printable}}
