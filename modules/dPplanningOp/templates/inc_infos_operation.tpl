{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $sejour->_id && !$sejour->_canRead}}
  <div class="small-info">{{tr}}COperation-msg-You do not have access to the details of the interventions.{{/tr}}</div>
  {{mb_return}}
{{/if}}

<script>
  printFicheAnesth = function (dossier_anesth_id, operation_id) {
    var url = new Url("cabinet", "print_fiche");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.addParam("operation_id", operation_id);
    url.popup(700, 500, "printFicheAnesth");
  };

  chooseAnesthCallback = function () {
    loadViewSejour({{$sejour->_id}});
  };

  printFicheBloc = function (operation_id) {
    var url = new Url("salleOp", "print_feuille_bloc");
    url.addParam("operation_id", operation_id);
    url.popup(700, 500, "printFicheBloc");
  };

  refreshListIntervs = function () {
    {{if !$sejour->_id}}
    return false;
    {{else}}
    var url = new Url("planningOp", "ajax_vw_operations_sejour");
    url.addParam("sejour_id", {{$sejour->_id}});
    url.requestUpdate("intervs-sejour-{{$sejour->_guid}}");
    {{/if}}
  };

  editVisite = function (operation_id) {
    var url = new Url("planningOp", "edit_visite_anesth");
    url.addParam("operation_id", operation_id);
    url.popup(800, 500, "editVisite");
  };
</script>

{{mb_default var=offline value=0}}
{{mb_default var=alert   value=0}}
{{mb_default var=with_thead value=0}}

{{assign var=colspan value=7}}
{{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
  {{assign var=colspan value=8}}
{{/if}}

<table class="tbl print_sejour me-table-card-list">
  {{if $with_thead}}
    {{mb_include module=soins template=inc_thead_dossier_soins colspan=$colspan}}
  {{/if}}

  <tr>
    <th class="title"
        colspan="{{$colspan}}">
      {{if $sejour->_ref_consult_anesth->_id && !$sejour->_ref_consult_anesth->operation_id}}
        <button style="float: right" class="print" type="button" onclick="printFicheAnesth('{{$sejour->_ref_consult_anesth->_id}}');">
          {{tr}}CAnesthPerop-action-Anesthesia sheet{{/tr}}
        </button>
      {{/if}}
      {{tr}}CSejour-back-operations{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}COperation-chir_id{{/tr}}</th>
    <th>{{tr}}COperation-anesth_id{{/tr}}</th>
    <th>{{tr}}Date{{/tr}}</th>
    <th>{{tr}}COperation-_ext_codes_ccam{{/tr}}</th>
    <th>{{tr}}COperation-cote{{/tr}}</th>
    {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
      <th>{{tr}}CBrancardage{{/tr}}</th>
    {{/if}}
    <th>{{tr}}common-Visit{{/tr}}</th>
    <th></th>
  </tr>
  <tbody id="intervs-sejour-{{$sejour->_guid}}">
  {{mb_include module=planningOp template=inc_info_list_operations}}
  </tbody>
</table>
