{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="consult_anesth" value=$selOp->_ref_consult_anesth}}
{{if !@$modeles_prat_id}}
  {{if $selOp->_ref_anesth->_id}}
    {{assign var="modeles_prat_id" value=$selOp->_ref_anesth->_id}}
  {{elseif $consult_anesth->_id}}
    {{assign var="modeles_prat_id" value=$consult_anesth->_ref_consultation->_ref_chir->_id}}
  {{/if}}
{{/if}}

<script>
  refreshAnesthPerops = function (operation_id) {
    var url = new Url("salleOp", "httpreq_vw_anesth_perop");
    url.addParam("operation_id", operation_id);
    url.requestUpdate("list_perops_" + operation_id);
  };

  refreshFicheAnesth = function (modale) {
    var url = new Url("cabinet", "print_fiche");

    {{if $consult_anesth->_id}}
    url.addParam("dossier_anesth_id", "{{$consult_anesth->_id}}");
    {{else}}
    url.addParam("operation_id", "{{$selOp->_id}}");
    {{/if}}

    url.addParam("offline", 0);
    url.addParam("print", 0);
    url.addParam("display", 1);
    url.addParam("pdf", 0);
    if (modale) {
      url.requestModal();
    } else {
      url.requestUpdate("fiche_anesth");
    }
  };

  printIntervAnesth = function () {
    var url = new Url("salleOp", "print_intervention_anesth");
    url.addParam("operation_id", "{{$selOp->_id}}");
    url.popup(800, 600, "Intervention anesthésiste");
  };

  refreshVisite = function (operation_id) {
    var url = new Url("salleOp", "ajax_refresh_visite_pre_anesth");
    url.addParam("operation_id", operation_id);
    url.addParam("callback", "refreshVisite");
    url.requestUpdate("visite_pre_anesth");
  };

  refreshFormsPerop = function () {
    ExObject.loadExObjects("{{$selOp->_class}}", "{{$selOp->_id}}", "forms_perop", 0);
  };

  Main.add(function () {
    if ($('anesth_tab_group')) {
      Control.Tabs.create('anesth_tab_group', true, {
        afterChange: function (container) {
          switch (container.id) {
            case "tab_perop":
              refreshAnesthPerops('{{$selOp->_id}}');
              break;
            case "perop":
              if (window.Prescription) {
                Prescription.updatePerop('{{$selOp->sejour_id}}');
              }
              break;
            case "fiche_anesth":
            {{if $special_model && $special_model->_id && $consult_anesth->_id}}
              ViewPort.SetAvlHeight('fiche_anesth', 1);
            {{else}}
              refreshFicheAnesth();
            {{/if}}
              break;
            case "forms_perop":
              refreshFormsPerop();
              break;
            case "anesth":
              reloadAnesth('{{$selOp->_id}}', null, null, 1);
          }
        }
      });
    }
  });
</script>

{{if $dialog}}
  {{assign var=onSubmit value="return onSubmitFormAjax(this, {onComplete: reloadAnesth})"}}
{{else}}
  {{assign var=onSubmit value="return checkForm(this)"}}
{{/if}}

<ul id="anesth_tab_group" class="control_tabs small">
  <li><a href="#anesth">{{tr}}CConsultAnesth-Anesthesia informations-court{{/tr}}</a>
  <li><a href="#tab_perop">{{tr}}COperation-Pre-operative event|pl{{/tr}}</a></li>
  {{if (((!"monitoringBloc"|module_active || !"monitoringBloc general active_graph_supervision"|gconf) && !$selOp->_ref_sejour->grossesse_id) ||
  ((!"monitoringMaternite"|module_active || !"monitoringMaternite general active_graph_supervision"|gconf) && $selOp->_ref_sejour->grossesse_id))}}
    <li><a href="#perop">{{tr}}CAdministration-Intraoperative administrations{{/tr}}</a></li>
  {{/if}}
  <li><a href="#fiche_anesth"
         {{if !$consult_anesth->_id}}class="wrong"{{/if}}>{{tr}}CAnesthPerop-action-Anesthesia sheet{{/tr}}</a></li>
  <li><a href="#tab_preanesth"
         {{if !$selOp->date_visite_anesth && (!$selOp->urgence && !'dPsalleOp COperation hide_visite_pre_anesth'|gconf)}}class="wrong"{{/if}}>{{tr}}CConsultAnesth-Pre Anesthesia{{/tr}}</a>
  </li>
  {{if "forms"|module_installed}}
    <li onmousedown="refreshFormsPerop()"><a href="#forms_perop">{{tr}}CExClass|pl{{/tr}}</a></li>
  {{/if}}
</ul>

<div id="anesth"></div>

<div id="fiche_anesth">
  {{if $special_model && $special_model->_id && $consult_anesth->_id}}
    <iframe src="?m=cabinet&raw=print_fiche&dossier_anesth_id={{$consult_anesth->_id}}&auto_print=0"
            style="width: 100%; height: 100%;"></iframe>
  {{/if}}
</div>

<div id="tab_preanesth" style="display: none;">
  {{mb_include module=salleOp template=inc_vw_visite_pre_anesth listAnesths=$listAnesths_preanesth}}
</div>

{{if (((!"monitoringBloc"|module_active || !"monitoringBloc general active_graph_supervision"|gconf) && !$selOp->_ref_sejour->grossesse_id) ||
((!"monitoringMaternite"|module_active || !"monitoringMaternite general active_graph_supervision"|gconf) && $selOp->_ref_sejour->grossesse_id))}}
  <div id="perop" style="display: none;"></div>
{{/if}}

<div id="tab_perop" style="display: none;">
  <table class="form me-margin-0 me-no-box-shadow">
    <tr>
      <th class="title" colspan="3">Per-operatoire</th>
    </tr>
    <tr>
      <th class="category">Evenements</th>
      <th class="category">Incidents</th>
      <th class="category"></th>
    </tr>
    <tr>
      <td style="width: 30%" class="me-valign-top">
        {{mb_include module="salleOp" template="inc_form_evenement_perop"}}
      </td>
      <td style="width: 30%" class="me-valign-top">
        {{mb_include module="salleOp" template="inc_form_evenement_perop" incident=1}}
      </td>
      <td id="list_perops_{{$selOp->_id}}">
      </td>
    </tr>
  </table>
</div>

{{if "forms"|module_installed}}
  <div id="forms_perop"></div>
{{/if}}
