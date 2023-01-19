{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=allow_edit_cleanup value=1}}

{{if $type == "reeval_antibio"}}
  <script>
    Control.Tabs.setTabCount('antibios_reeval', {{$prescriptions|@count}});
  </script>
  <div class="{{if $prescriptions|@count}}small-warning{{else}}small-info{{/if}}">
    {{$prescriptions|@count}} séjours contiennent des <strong>réévaluations en retard ou prévues</strong> dans les prochaines 12h.
  </div>
{{else}}
  <script>
    Control.Tabs.setTabCount('com_pharma', {{$prescriptions|@count}});
  </script>
{{/if}}

<table class="tbl me-small">
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

  {{foreach from=$prescriptions item=_prescription}}
    {{assign var=sejour value=$_prescription->_ref_object}}
    {{assign var=patient value=$_prescription->_ref_patient}}
    <tr>
      {{mb_include module=soins template=inc_vw_sejour lite_view=true prescription=$_prescription service_id="" show_affectation=true show_full_affectation=true}}
    </tr>
  {{foreachelse}}
    <tr><td colspan="10" class="empty">{{tr}}CPrescription.none{{/tr}}</td></tr>
  {{/foreach}}
</table>
