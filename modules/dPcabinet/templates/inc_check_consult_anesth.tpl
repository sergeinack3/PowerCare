{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=need_more_atc value=0}}
<script>
  completeElem = function(fragment, mousedown){
    Control.Modal.close();
    var tab_ipaqss = Control.Tabs.create('tab-consult-anesth');
    tab_ipaqss.setActiveTab(fragment);
    var link = tab_ipaqss.activeLink.up('li');
    if (mousedown && link.onmousedown) {
      link.onmousedown();
    }
  };
  clotureConsultAnesth = function (type, store_pdf) {
    var form = document.formCheckConsultAnesth;
    onSubmitFormAjax(form, {
      onComplete : function () {
        var callback = function() {
          if (type == 'normal') {
            document.location.reload();
          }
          else {
            Control.Modal.close();
            GestionDA.edit();
          }
        };
        if ($V(form._store_fiche_pdf)) {
          storePdfFicheAnesth($V(form._store_fiche_pdf), callback);
        }
        else {
          callback();
        }
      }}
    );
  };
  storePdfConsultAnesth = function (store_pdf) {
    var print_cs_anesth_pdf = $('print_cs_anesth_pdf');
    if (store_pdf == '1' && print_cs_anesth_pdf) {
      print_cs_anesth_pdf.onclick();
    }
  };
  storePdfFicheAnesth = function (dossier_anesth_id, callback) {
    return onSubmitFormAjax(getForm('storePdfFicheAnesth-'+dossier_anesth_id), callback);
  };
</script>

  <style>
    button img{
      width:16px;
      height:16px;
    }
  </style>

<table class="main layout">
  <tr>
    <td>
      <fieldset>
        <legend>IPAQSS</legend>
        <table class="tbl">
          <tr>
            <th>Critère</th>
            {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
              {{if $consult_anesth->_ref_operation->_id}}
                <th class="text" style="width:13em;">{{$consult_anesth->_ref_operation->_view}}</th>
              {{else}}
                <th style="width:13em;">Pas d'intervention</th>
              {{/if}}
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRank1');">
              <div class="rank">1</div><strong>Identification du patient</strong>
            </td>
            {{foreach from=$tab_op item=num_operation}}
              <td class="button"><img src="images/icons/note_green.png"/></td>
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRank2');">
              <div class="rank">2</div><strong>Medecin anesthésiste</strong>
            </td>
            {{foreach from=$tab_op item=num_operation}}
              <td class="button"><img src="images/icons/note_green.png"/></td>
            {{/foreach}}
          </tr>
          <tr>
            {{assign var="result" value=false}}
            {{if $dm_patient->absence_traitement || $dm_patient->_ref_traitements|@count ||
            ($dm_patient->_ref_prescription && $dm_patient->_ref_prescription->_ref_prescription_lines|@count)}}
              {{assign var="result" value=true}}
            {{/if}}
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRank4');"><div class="rank">4</div><strong>Traitement habituel</strong></td>
            {{foreach from=$tab_op item=num_operation}}
              <td class="button">
                <img src="images/icons/note_{{if $result}}green{{else}}red{{/if}}.png"/>
                {{if !$result}}
                  <button type="button" class="edit notext" onclick="completeElem('AntTrait', 0);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                {{/if}}
              </td>
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRank5');"><div class="rank">5</div><strong>Risque anesthésique</strong></td>
            {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
              {{assign var="operation_id" value=$consult_anesth->operation_id}}
              {{assign var="dm_sejour" value=false}}

              {{if $operation_id}}
                {{assign var="dm_sejour" value=$consult_anesth->_ref_operation->_ref_sejour->_ref_dossier_medical}}
              {{/if}}

              {{assign var="result" value=false}}
              {{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf &&
              ((($dm_sejour && $dm_sejour->_id &&
              ($dm_sejour->risque_antibioprophylaxie != "NR" || $dm_sejour->risque_prophylaxie != "NR" ||
              $dm_sejour->risque_MCJ_chirurgie != "NR" || $dm_sejour->risque_thrombo_chirurgie != "NR"))
              || ($dm_patient->_id && ($dm_patient->facteurs_risque != "" ||
              $dm_patient->risque_thrombo_patient != "NR" || $dm_patient->risque_MCJ_patient != "NR"))))}}
                {{assign var="result" value=true}}
              {{/if}}

              <td class="button">
                {{if $use_moebius || "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
                  <img src="images/icons/note_{{if $use_moebius || $result}}green{{else}}red{{/if}}.png"/>
                  {{if !$use_moebius && !$result}}
                    <button type="button" class="edit notext" onclick="completeElem('facteursRisque', 1);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                  {{/if}}
                {{else}}
                  <img src="images/icons/note_orange.png" title="N/A"/>
                {{/if}}
              </td>
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRank6');"><div class="rank">6</div><strong>Type d'anesthésie</strong></td>
            {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
              {{if $consult_anesth->_ref_operation->_id}}
                <td class="button">
                  <img src="images/icons/note_{{if $consult_anesth->_ref_operation->type_anesth}}green{{else}}red{{/if}}.png"/>
                  {{if !$consult_anesth->_ref_operation->type_anesth}}
                    <button type="button" class="edit notext" onclick="completeElem('InfoAnesth', 1);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                  {{/if}}
                </td>
              {{else}}
                <td class="button">
                  <img src="images/icons/note_{{if $consult_anesth->type_anesth}}green{{else}}red{{/if}}.png"/>
                  {{if !$consult_anesth->type_anesth}}
                    <button type="button" class="edit notext" onclick="completeElem('InfoAnesth', 1);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                  {{/if}}
                </td>
              {{/if}}
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRank7');">
              <div class="rank">7</div><strong>Voies aériennes supérieures</strong>
            </td>
            {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
              {{assign var="result" value=false}}
              {{if ($consult_anesth->mallampati && ($consult_anesth->bouche || $consult_anesth->bouche_enfant)
                  && $consult_anesth->distThyro && $consult_anesth->mob_cervicale) || $consult_anesth->conclusion}}
                {{assign var="result" value=true}}
              {{/if}}
              <td class="button">
                <img src="images/icons/note_{{if $result}}green{{else}}red{{/if}}.png"/>
                {{if !$result}}
                  <button type="button" class="edit notext" onclick="completeElem('Intub', 0);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                {{/if}}
              </td>
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRankPoids');"><div class="rank"></div><strong>Poids</strong></td>
            {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
              {{assign var="result" value=false}}
              {{if $consult_anesth->_ref_consultation->_ref_patient->_ref_constantes_medicales->poids}}
                {{assign var="result" value=true}}
              {{/if}}
              <td class="button">
                <img src="images/icons/note_{{if $result}}green{{else}}red{{/if}}.png"/>
                {{if !$result}}
                  <button type="button" class="edit notext" onclick="completeElem('constantes-medicales', 1);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                {{/if}}
              </td>
            {{/foreach}}
          </tr>
          <tr>
            <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRankASA');"><div class="rank"></div><strong>Score ASA</strong></td>
            {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
              {{if $consult_anesth->_ref_operation->_id}}
                <td class="button">
                  <img src="images/icons/note_{{if $consult_anesth->_ref_operation->ASA}}green{{else}}red{{/if}}.png"/>
                  {{if !$consult_anesth->_ref_operation->ASA}}
                    <button type="button" class="edit notext" onclick="completeElem('InfoAnesth', 1);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                  {{/if}}
                </td>
              {{else}}
                <td class="button">
                  <img src="images/icons/note_{{if $consult_anesth->ASA}}green{{else}}red{{/if}}.png"/>
                  {{if !$consult_anesth->ASA}}
                    <button type="button" class="edit notext" onclick="completeElem('InfoAnesth', 1);" style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>
                  {{/if}}
                </td>
              {{/if}}
            {{/foreach}}
          </tr>
          {{if "dPplanningOp CSejour show_circuit_ambu"|gconf}}
            <tr>
              <td onmouseover="ObjectTooltip.createDOM(this, 'DetailRankCircuit');"><div class="rank"></div><strong>Type de circuit</strong></td>
              {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth}}
                {{assign var=sejour value=$consult_anesth->_ref_operation->_ref_sejour}}
                {{assign var=result value=false}}

                {{if $sejour && $sejour->_id && $sejour->circuit_ambu}}
                  {{assign var=result value=true}}
                {{/if}}
                <td class="button">
                  <img src="images/icons/note_{{if $result}}green{{else}}red{{/if}}.png"/>
                  {{if $result}}
                    <button type="button" class="edit notext"
                            onclick="Modal.open('circuit_ambu_{{$sejour->_id}}', {width: 400, showClose: true, onClose: Control.Modal.refresh} );"
                            style="float:right;margin-left:-20px;">{{tr}}Modify{{/tr}}</button>

                    <div id="circuit_ambu_{{$sejour->_id}}" style="display: none;">
                      <form name="actionPat" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
                        {{mb_key   object=$sejour}}
                        {{mb_class object=$sejour}}
                        <table class="tbl form">
                          <tr>
                            <th class="title" colspan="2">{{tr}}CProtocole-Modification of the type of ambulatory circuit{{/tr}}</th>
                          </tr>
                          <tr>
                            <th>{{mb_label object=$sejour field=circuit_ambu}}</th>
                            <td>{{mb_field object=$sejour field=circuit_ambu typeEnum=radio onchange="this.form.onsubmit();"}}</td>
                          </tr>
                        </table>
                      </form>
                    </div>
                  {{/if}}
                </td>
              {{/foreach}}
            </tr>
          {{/if}}
          </table>
      </fieldset>
    </td>
    {{if $conf.dPpatients.CAntecedent.mandatory_types}}
      <td class="me-check-consult-anesth-mandatory-atcd">
        <fieldset>
          <legend>Antécédents obligatoires</legend>
            <table class="tbl">
              {{foreach from=$mandatory_types key=_type item=_atc}}
                <tr>
                  <td>
                    <strong>{{tr}}CAntecedent.type.{{$_type}}{{/tr}}</strong>
                  </td>
                  <td class="button">
                    <img src="images/icons/note_{{if $_atc|@count}}green{{else}}red{{/if}}.png" />
                    {{if !$_atc|@count}}
                      <button type="button" class="edit notext" onclick="completeElem('AntTrait', 1);" style="float:right;">{{tr}}Modify{{/tr}}</button>
                    {{/if}}
                  </td>
                </tr>
              {{/foreach}}
            </table>

        </fieldset>
      </td>
    {{/if}}
  </tr>
</table>

<p style="text-align: center">
  {{math equation="x+1" x=$tab_op|@count assign=colonnes}}
    <button type="button" class="undo" onclick="Control.Modal.close();">Continuer la consultation</button>

    {{assign var=print_to_pdf value="dPcabinet CConsultAnesth stock_pdf_anesth_on_close"|gconf}}
    {{if $consult->chrono <= $consult|const:'EN_COURS' && !$need_more_atc}}
      <button type="button" class="tick oneclick" onclick="clotureConsultAnesth('normal', '{{$print_to_pdf}}');">Terminer la consultation</button>
      {{if $consult->_refs_dossiers_anesth|@count > 1 || $op_sans_dossier_anesth}}
        <br/>
        <button type="button" class="edit oneclick" onclick="clotureConsultAnesth('edit', '{{$print_to_pdf}}');">
          Terminer la consultation et gérer les dossiers suivants
        </button>
      {{/if}}
    {{elseif $consult->_refs_dossiers_anesth|@count > 1 || $op_sans_dossier_anesth}}
      <button type="button" class="edit" onclick="Control.Modal.close();GestionDA.edit();">
        Gérer les dossiers suivants
      </button>
    {{/if}}
</p>

<form class="watch" name="formCheckConsultAnesth" action="?m={{$m}}" method="post">
  <input type="hidden" name="m" value="dPcabinet" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  {{mb_key   object=$consult}}
  <input type="hidden" name="chrono" value="{{$consult|const:'TERMINE'}}" />
  <input type="hidden" name="_store_fiche_pdf" value="{{$dossier_anesth->_id}}" />
</form>

{{if $dossier_anesth->_id}}
  <form class="watch" name="storePdfFicheAnesth-{{$dossier_anesth->_id}}" action="?" method="post">
    {{mb_class object=$dossier_anesth}}
    {{mb_key   object=$dossier_anesth}}
    <input type="hidden" name="_store_fiche_pdf" value="1" />
  </form>
{{/if}}

{{mb_include module=cabinet template=vw_legend_check_anesth}}
