{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=allow_edit_cleanup value=1}}

{{if $board}}
  {{mb_default var=default_id value='prescriptions_non_signees'}}
  <script>
    Control.Tabs.setTabCount('{{$default_id}}', {{$counter_prescription}});
  </script>
{{/if}}

<table class="tbl me-small">
  {{if $board}}
    <tr>
      <th rowspan="2" class="narrow">{{mb_title class=CLit field=chambre_id}}</th>
      {{if "hotellerie"|module_active && $allow_edit_cleanup}}
        <th rowspan="2" class="narrow">{{tr}}CBedCleanup-Cleaning{{/tr}}</th>
      {{/if}}
      <th colspan="2" rowspan="2" class="">
        {{mb_title class=CPatient field=nom}}
        <br />
        ({{mb_title class=CPatient field=nom_jeune_fille}})
      </th>
      {{if "dPImeds"|module_active}}
        <th rowspan="2" class="">Labo</th>
      {{/if}}
      <th colspan="2" class="">Alertes</th>
      <th rowspan="2" class="narrow">{{mb_title class=CSejour field=entree}}</th>
      <th rowspan="2" class="">{{mb_title class=CSejour field=libelle}}</th>
      <th rowspan="2" class="">Prat.</th>
    </tr>
    <tr>
      <th class="">All.</th>
      <th class=""><label title="{{tr}}CAntecedent.more{{/tr}}">Atcd</label></th>
    </tr>
  {{else}}
  <tr>
    <th>{{tr}}CPrescription|pl{{/tr}} ({{$counter_prescription}})</th>
  </tr>
  {{/if}}

  {{foreach from=$prescriptions_by_praticiens_type key=praticien_type item=prescriptions}}
    {{if $board && ($default_id == "prescriptions_non_signees")}}
      <tr>
        <th class="section" colspan="10">{{$praticien_type}}</th>
      </tr>
    {{/if}}
    {{foreach from=$prescriptions item=_prescription}}
      {{assign var=sejour value=$_prescription->_ref_object}}
      {{assign var=patient value=$_prescription->_ref_patient}}

      {{if $board}}
      <tr>
        {{mb_include module=soins template=inc_vw_sejour lite_view=true prescription=$_prescription service_id="" show_affectation=true show_full_affectation=true}}
        </tr>
      {{else}}
      <tr>
        <td class="text">
         <a href="#{{$_prescription->_id}}" onclick="loadSejour('{{$_prescription->object_id}}'); Prescription.reloadPrescSejour('{{$_prescription->_id}}','','','','',''); return false;">
            {{$_prescription->_ref_patient->_view}}
         </a>
        </td>
      </tr>
      {{/if}}
    {{/foreach}}
  {{foreachelse}}
    <tr><td colspan="9" class="empty">{{tr}}CPrescription.none{{/tr}}</td></tr>
  {{/foreach}}
</table>
