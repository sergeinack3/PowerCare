{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=ssr script=planification}}

{{assign var=print_paysage_sejour_ssr value="ssr print_week print_paysage_sejour_ssr"|gconf}}
{{if $print_paysage_sejour_ssr}}
  <style>
    @media print {
      @page {
        size: landscape;
      }
      .planning.large .hours td {
        height: 75px;
      }
      .week-container {
        overflow: hidden !important;
      }
    }
  </style>
{{/if}}

<script>
  Main.add(function(){
    var height_planning = '{{$full_screen}}' ? document.viewport.getHeight()-50 : 600;
    Planification.current_m = '{{$m}}';
    Planification.refreshSejour("{{$sejour->_id}}", false, height_planning, true, true, {{$current_day}});
  });
</script>

{{if !$full_screen}}
  <button type="button" class="print not-printable" style="float:right;" onclick="window.print();">
    {{tr}}Print{{/tr}}
  </button>
  <h3>
    {{tr}}CBilanSSR-kine_id{{if $m == "psy"}}-{{$m}}{{/if}}{{/tr}} :
    {{$sejour->_ref_bilan_ssr->_ref_technicien->_ref_kine}}
  </h3>
{{/if}}

{{if $print_paysage_sejour_ssr}}
  <div id="planning-sejour" style="page-break-after: always;"></div>
{{/if}}

<table class="tbl" {{if $full_screen}}style="display: none;" {{/if}}>
  <tr>
    <th class="title" colspan="2">{{tr}}CIntervenantCdARR|pl{{/tr}}</th>
  </tr>
  {{foreach from=$intervenants item=_intervenant_by_elt key=element_id}}
    {{assign var=element value=$elements.$element_id}}
    <tr>
      <th>{{$element->_view}}</th>
     <td>
    {{foreach from=$_intervenant_by_elt item=_intervenant name="intervenants"}}
       {{$_intervenant->_view}}
       {{if !$smarty.foreach.intervenants.last}},{{/if}}
    {{/foreach}}
    </td>
   </tr> 
  {{/foreach}}
</table>

{{if !$print_paysage_sejour_ssr}}
  <div id="planning-sejour"></div>
{{/if}}

{{if "ssr print_week see_contrat"|gconf}}
  <div class="planning-signatures">
    <fieldset style="float: left;">
      <legend>{{tr}}CPatient{{/tr}}</legend>
      {{tr var1=$sejour->_ref_patient}}CEvenementSSR.msg_contrat of %s{{/tr}}
    </fieldset>

    <fieldset style="float: right;">
      <legend>{{tr}}common-Practitioner{{/tr}}</legend>
      {{tr}}CEvenementSSR.chir_ref{{/tr}} :
      <br/>{{$sejour->_ref_praticien}}
    </fieldset>
  </div>
{{/if}}