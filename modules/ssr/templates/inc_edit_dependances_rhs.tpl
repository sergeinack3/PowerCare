{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dependances value=$rhs->_ref_dependances}}
<form name="dependances-{{$rhs->_guid}}" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_key object=$dependances}}
  {{mb_class object=$dependances}}
  <input type="hidden" name="del" value="0" />
  {{mb_field object=$dependances field=rhs_id hidden=true}}
  <table class="form tbl">
    <tr>
      <th class="title" colspan="3">
        {{$rhs->_ref_sejour->_ref_patient}}
      </th>
    </tr>
    <tr>
      <th class="category me-text-align-left">{{tr}}Category{{/tr}}</th>
      <th class="category me-text-align-left">{{tr}}CDependancesRHS-questions{{/tr}}</th>
      <th class="category me-text-align-left">{{tr}}CDependancesRHS.degre{{/tr}}</th>
    </tr>
    <tr>
      <th rowspan="2" class="me-valign-top">{{tr}}DependancesRHSBilan-habillage{{/tr}}</th>
      <td>{{mb_label object=$dependances field=habillage_haut}}</td>
      <td>{{mb_field object=$dependances field=habillage_haut tabindex="10001" onchange="this.form.onsubmit()" typeEnum="radio"}}</td>
      <tr>
        <td>{{mb_label object=$dependances field=habillage_bas}}</td>
        <td>{{mb_field object=$dependances field=habillage_bas tabindex="10002" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
    </tr>
    <tr>
      <th rowspan="5" class="me-valign-top">{{tr}}DependancesRHSBilan-deplacement{{/tr}}</th>
      <td>{{mb_label object=$dependances field=deplacement_transfert_lit_chaise}}</td>
      <td>{{mb_field object=$dependances field=deplacement_transfert_lit_chaise tabindex="10003" onchange="this.form.onsubmit()" typeEnum="radio"}}</td>
      <tr>
        <td>{{mb_label object=$dependances field=deplacement_transfert_toilette}}</td>
        <td>{{mb_field object=$dependances field=deplacement_transfert_toilette tabindex="10004" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
      <tr>
        <td>{{mb_label object=$dependances field=deplacement_transfert_baignoire}}</td>
        <td>{{mb_field object=$dependances field=deplacement_transfert_baignoire tabindex="10005" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
      <tr>
        <td>{{mb_label object=$dependances field=deplacement_locomotion}}</td>
        <td>{{mb_field object=$dependances field=deplacement_locomotion tabindex="10006" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
      <tr>
        <td>{{mb_label object=$dependances field=deplacement_escalier}}</td>
        <td>{{mb_field object=$dependances field=deplacement_escalier tabindex="10007" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
    </tr>
    <tr>
      <th rowspan="3" class="me-valign-top">{{tr}}DependancesRHSBilan-alimentation{{/tr}}</th>
      <td>{{mb_label object=$dependances field=alimentation_utilisations_ustensile}}</td>
      <td>{{mb_field object=$dependances field=alimentation_utilisations_ustensile tabindex="10008" onchange="this.form.onsubmit()" typeEnum="radio"}}</td>
      <tr>
        <td>{{mb_label object=$dependances field=alimentation_mastication}}</td>
        <td>{{mb_field object=$dependances field=alimentation_mastication tabindex="10009" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
      <tr>
        <td>{{mb_label object=$dependances field=alimentation_deglutition}}</td>
        <td>{{mb_field object=$dependances field=alimentation_deglutition tabindex="10010" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
    </tr>
    <tr>
      <th rowspan="2" class="me-valign-top">{{tr}}DependancesRHSBilan-continence{{/tr}}</th>
      <td>{{mb_label object=$dependances field=continence_controle_miction}}</td>
      <td>{{mb_field object=$dependances field=continence_controle_miction tabindex="10011" onchange="this.form.onsubmit()" typeEnum="radio"}}</td>
      <tr>
        <td>{{mb_label object=$dependances field=continence_controle_defecation}}</td>
        <td>{{mb_field object=$dependances field=continence_controle_defecation tabindex="10012" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
      </tr>
    </tr>
    <tr>
      <th>{{tr}}DependancesRHSBilan-comportement{{/tr}}</th>
      <td>{{mb_label object=$dependances field=comportement}}</td>
      <td>{{mb_field object=$dependances field=comportement tabindex="10013" onchange="this.form.onsubmit()" typeEnum="radio"}}</td>
    </tr>
    <tr>
      <th rowspan="2" class="me-valign-top">{{tr}}DependancesRHSBilan-relation{{/tr}}</th>
      <td>{{mb_label object=$dependances field=relation_comprehension_communication}}</td>
      <td>{{mb_field object=$dependances field=relation_comprehension_communication tabindex="10014" onchange="this.form.onsubmit()" typeEnum="radio"}}</td>
    <tr>
      <td>{{mb_label object=$dependances field=relation_expression_claire}}</td>
      <td>{{mb_field object=$dependances field=relation_expression_claire tabindex="10015" onchange="this.form.onsubmit()" typeEnum="radio"}}</td></tr>
    </tr>
    </tr>
  </table>
</form>  
