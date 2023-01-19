{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="bloodSalvage" script="bloodSalvage"}}
{{mb_script module="planningOp" script="operation"}}
{{mb_script module="dPsalleOp" script="salleOp" ajax=true}}

{{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
  {{mb_script module=brancardage script=brancardage ajax=true}}
{{/if}}

{{if "dPprescription"|module_active}}
  {{mb_script module="prescription" script="prescription"}}
  {{mb_script module="prescription" script="element_selector"}}
{{/if}}

{{if "dPmedicament"|module_active}}
  {{mb_script module="medicament" script="medicament_selector"}}
  {{mb_script module="medicament" script="equivalent_selector"}}
{{/if}}

{{mb_script module=cim10 script=CIM}}
{{mb_script module="compteRendu" script="document"}}
{{mb_script module="compteRendu" script="modele_selector"}}
{{mb_script module="files" script="file"}}

{{if $isImedsInstalled}}
  {{mb_script module="Imeds" script="Imeds_results_watcher"}}
{{/if}}

{{assign var=use_sortie_reveil_reel value="dPsalleOp COperation use_sortie_reveil_reel"|gconf}}
{{assign var=password_sortie value="dPsalleOp COperation password_sortie"|gconf}}

{{assign var=use_concentrator value=false}}
{{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringConcentrator active"|gconf}}
  {{assign var=use_concentrator value=true}}
{{/if}}

<style>
  input.seek_patient {
    float: right;
  }
</style>

<script>
  updateNbReveil = function (date, bloc_id, sspi_id) {
    new Url('salleOp', 'ajax_count_reveil')
      .addParam('date', date)
      .addParam('bloc_id', bloc_id)
      .addParam('sspi_id', sspi_id)
      .requestJSON(function (count) {
        for (var tabName in count) {
          var result = count[tabName];
          var total = null;

          {{if $use_sortie_reveil_reel}}
          if (tabName == "out" || tabName == "reveil") {
            result = count[tabName][1];
            total = count[tabName][0];
          }
          {{/if}}
          Control.Tabs.setTabCount(tabName, result, total);
        }
      });
  };

  Main.add(function () {
    ObjectTooltip.modes.allergies = {
      module: "patients",
      action: "ajax_vw_allergies",
      sClass: "tooltip"
    };

    window.reveil_tabs = Control.Tabs.create('reveil_tabs', false, {
      afterChange: function (container) {
        switch (container.id) {
          case 'preop':
          case 'encours':
          case 'ops':
          case 'reveil':
          case 'out':
            refreshTabReveil(container.id);
            break;
        }
      }
    });
  });

  EditCheckList = {
    url:  null,
    edit: function (bloc_id, date, type, multi_ouverture, sspi_id) {
      var url = new Url('salleOp', 'ajax_edit_checklist');
      url.addParam('date', date);
      url.addParam('bloc_id', bloc_id);
      url.addParam('salle_id', 0);
      url.addParam('type', type);
      url.addParam('sspi_id', sspi_id);
      if (multi_ouverture) {
        url.addParam('multi_ouverture', multi_ouverture);
      }
      url.requestModal();
      url.modalObject.observe("afterClose", function () {
        location.reload();
      });
    }
  };

  function refreshTabReveil(type, order_col, order_way) {
    var form = getForm('selectBloc');

    var url = new Url('salleOp', 'httpreq_reveil')
      .addParam('bloc_id', '{{$bloc->_id}}')
      .addParam('date', '{{$date}}')
      .addParam('type', type)
      .addNotNullParam('order_col', order_col)
      .addNotNullParam('order_way', order_way);
    if (type === 'ops' || type === 'reveil' || type === 'preop') {
      url.addParam('sspi_id', $V(form.sspi_id));
    }

    var elt = $(type);

    SalleOp.setUpdater(elt, url);

    if (form.sspi_id) {
      form.sspi_id[(elt.id === 'preop' || elt.id === 'ops' || elt.id === 'reveil' || elt.id === 'out') ? 'show' : 'hide']();
    }

    updateNbReveil('{{$date}}', '{{$bloc->_id}}', $V(form.sspi_id));
  }

  orderTabReveil = function (col, way, type) {
    refreshTabReveil(type, col, way);
  };

  showDossierSoins = function (sejour_id, operation_id, default_tab) {
    {{if "dPprescription"|module_active}}
    var url = new Url("soins", "viewDossierSejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("operation_id", operation_id);
    url.addParam("modal", 0);
    if (default_tab) {
      url.addParam("default_tab", default_tab);
    }
    url.modal({width: "95%", height: "95%"});
    modalWindow = url.modalObject;
    {{/if}}
  };

  printDossier = function (sejour_id, operation_id) {
    var url = new Url("hospi", "httpreq_documents_sejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("operation_id", operation_id);
    url.requestModal(700, 400);
  };

  callbackSortie = function (user_id) {
    if (!window.current_form) {
      return;
    }
    var form = window.current_form;
    $V(form.sortie_locker_id, form.sortie_reveil_possible.value ? user_id : '');
    submitReveilForm(form);
    Control.Modal.close();
  };

  seekPatient = function (input) {
    var value = $V(input);
    var field = $(input).up('table').select('span.CPatient-view');

    field.each(function (e) {
      if (!value) {
        e.up('tr').show();
      } else {
        if (!e.getText().like(value)) {
          e.up('tr').hide();
        } else {
          e.up('tr').show();
        }
      }
    });
  };

  submitReveilForm = function (oFormOperation, askPoste, concentrator_session, current_session_id) {
    var callback = function () {
      onSubmitFormAjax(oFormOperation, refreshTabReveil.curry('reveil'));
    };

    var openPosteConcentrator = function () {
      App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
        ConcentratorCommon.askPosteConcentrator(
          $V(oFormOperation.operation_id),
          "{{$bloc->_id}}",
          "sspi",
          oFormOperation,
          callback(),
          current_session_id ? 0 : 1
        );
        if (oFormOperation.elements['sortie_reveil_reel'] && $V(oFormOperation.elements['sortie_reveil_reel']) != '') {
          ConcentratorCommon.importDataToConstants($V(oFormOperation.operation_id), 'sspi');
        }
      });
    };

    {{if "patientMonitoring"|module_active && $use_concentrator}}
      if (current_session_id) {
        var stop_session = confirm($T('CMonitoringConcentrator-msg-Do you want to stop session in progress'));

        if (stop_session) {
          App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
            ConcentratorCommon.stopCurrentSession($V(oFormOperation.operation_id), function () {
              openPosteConcentrator();
            });
          });
        }
      }
      else {
        if (askPoste && concentrator_session) {
          openPosteConcentrator();
        } else {
          callback();
        }
      }
    {{else}}
    callback();
    {{/if}}
  };

  submitReveil = function (form) {
    var callback = function () {
      {{if $password_sortie && (!$is_anesth || !$app->user_prefs.autosigne_sortie)}}
      window.current_form = form;
      var url = new Url("salleOp", "ajax_lock_sortie");
      url.requestModal("30%", "20%", {
        onClose:    function () {
          $V(form.sortie_reveil_possible_da, '', false);
          $V(form.sortie_reveil_possible, '', false);
        },
        onComplete: function () {
          // Pré-selection si anesthésiste dans la modale de saisie du mot de passe
          {{if $is_anesth}}
          var form_sortie = getForm("lock_sortie");
          $V(form_sortie.user_id, '{{$app->user_id}}');
          {{/if}}
        }
      });
      {{else}}
      $V(form.sortie_locker_id, '{{$app->user_id}}');
      submitReveilForm(form);
      {{/if}}
    };

    callback();
  };

  submitSortieForm = function (oFormSortie) {
    onSubmitFormAjax(oFormSortie, function () {
      refreshTabReveil('reveil');
      refreshTabReveil('out');
    });
  };

  submitSortie = function (form) {
    {{if $password_sortie && (!$is_anesth || !$app->user_prefs.autosigne_sortie)}}
    window.current_form = form;
    var url = new Url("salleOp", "ajax_lock_sortie");
    url.requestModal("30%", "20%", {
      onComplete: function () {
        {{if $is_anesth}}
        var form_sortie = getForm("lock_sortie");
        $V(form_sortie.user_id, '{{$app->user_id}}');
        {{/if}}
      }
    });
    {{else}}
    submitSortieForm(form);
    {{/if}}
  };
</script>

<ul id="reveil_tabs" class="control_tabs">
  <li>
    <a class="empty" href="#preop">{{tr}}SSPI.Preop{{/tr}} <small>(&ndash;)</small></a>
  </li>
  <li>
    <a class="empty" href="#encours">{{tr}}SSPI.Encours{{/tr}} <small>(&ndash;)</small></a>
  </li>
  <li>
    <a class="empty" href="#ops">{{tr}}SSPI.Attente{{/tr}} <small>(&ndash;)</small></a>
  </li>
  <li>
    <a class="empty" href="#reveil">{{tr}}SSPI.Reveil{{/tr}} <small>(&ndash;)</small></a>
  </li>
  <li>
    <a class="empty" href="#out">{{tr}}SSPI.Sortie{{/tr}} <small>(&ndash;)</small></a>
  </li>

  <li style="float:right; font-weight: bold;" class="me-max-width-100 me-float-none">
    {{mb_include template=inc_filter_reveil}}
  </li>
</ul>

<div id="preop" style="display: none;" class="me-padding-0 me-table-reveil"></div>
<div id="encours" style="display: none;" class="me-padding-0 me-table-reveil"></div>
<div id="ops" style="display: none;" class="me-padding-0 me-table-reveil"></div>
<div id="reveil" style="display: none;" class="me-padding-0 me-table-reveil"></div>
<div id="out" style="display: none;" class="me-padding-0 me-table-reveil"></div>
