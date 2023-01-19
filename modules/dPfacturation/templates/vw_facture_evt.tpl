{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=praticien value=$evenement->_ref_praticien}}
{{assign var=object value=$evenement}}

<script>
  pursueTarif = function() {
    var form = document.tarifFrm;
    $V(form.tarif, "pursue");
    $V(form.valide, 0);
    Facture.submitEvt(form, '{{$evenement->_guid}}', false);
  };

  cancelTarif = function(action, callback) {
    var form = document.tarifFrm;

    if(action == "delActes") {
      $V(form._delete_actes, 1);
      $V(form.tarif, "");
    }

    $V(form.valide, 0);
    Facture.submitEvt(form, '{{$evenement->_guid}}', true, callback);
  };

  validTarif = function(){
    var form = document.tarifFrm;

    if ($V(form.tarif) == ""){
      $V(form.tarif, "manuel");
    }
    Facture.submitEvt(form, '{{$evenement->_guid}}', true);
  };

  reloadFacture = function() {
    Facture.reload('{{$evenement->_ref_patient->_id}}', '{{$evenement->_id}}', 1, '{{$evenement->_ref_facture->_id}}', '{{$evenement->_ref_facture->_class}}');
  };

  checkActe = function() {
    cancelTarif(null, reloadFacture);
  };

  Main.add(function() {
    if (window.tabsConsult || window.tabsConsultAnesth) {
      Control.Tabs.setTabCount("reglement", "{{$facture->_ref_reglements|@count}}");
    }
    Facture.evenement_guid = '{{$evenement->_guid}}';
    Facture.evenement_id = '{{$evenement->_id}}';
    Facture.user_id = '{{$evenement->_ref_praticien->_id}}';
  });
</script>

{{if $frais_divers|@count}}
  <form name="addFactureDivers" action="" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Facture.reloadEvt('{{$evenement->_guid}}', true);}});">
    {{math equation="x+1" x=$evenement->_ref_factures|@count assign=numero_fact}}
    {{mb_class  object=$evenement->_ref_facture}}
    <input type="hidden" name="facture_id"    value=""/>
    <input type="hidden" name="group_id"      value="{{$g}}"/>
    <input type="hidden" name="patient_id"    value="{{$evenement->_ref_facture->patient_id}}"/>
    <input type="hidden" name="praticien_id"  value="{{$evenement->_ref_facture->praticien_id}}"/>
    <input type="hidden" name="_evt_id"   value="{{$evenement->_id}}"/>
    <input type="hidden" name="ouverture"     value="{{$evenement->_ref_facture->ouverture}}"/>
    <input type="hidden" name="numero"        value="{{$numero_fact}}"/>
  </form>
{{/if}}

<table class="form">
  <tr>
    <td>
      <fieldset>
        <legend>{{tr}}CConsultation-cotation{{/tr}}</legend>
        <!-- Formulaire de selection de tarif -->
        <form name="selectionTarif" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Facture.reloadEvt('{{$evenement->_guid}}', true);}});">
          {{mb_key object=$evenement}}
          {{mb_class object=$evenement}}
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="_patient_id" value="{{$evenement->_ref_patient->_id}}" />
          <input type="hidden" name="_bind_tarif" value="1" />
          {{if $evenement->tarif == "pursue"}}
            {{mb_field object=$evenement field=tarif hidden=1}}
          {{/if}}

          <table class="form me-small-form me-no-box-shadow">
            {{if (!$evenement->tarif || $evenement->tarif == "pursue") && !$evenement->valide}}
              <tr>
                <th style="width: 225px;">
                  <label for="choix" title="{{tr}}CConsultation-cotation-desc{{/tr}}">
                    {{tr}}CConsultation-cotation{{/tr}}
                  </label>
                </th>
                <td>
                  <select name="_tarif_id"  class="notNull str" style="width: 130px;" onchange="this.form.onsubmit();">
                    <option value="" selected="selected">&mdash; {{tr}}Choose{{/tr}}</option>
                    {{if $tarifs.user|@count}}
                      <optgroup label="{{tr}}CConsultation-Practitioner price{{/tr}}">
                        {{foreach from=$tarifs.user item=_tarif}}
                          <option value="{{$_tarif->_id}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                        {{/foreach}}
                      </optgroup>
                    {{/if}}
                    {{if $tarifs.func|@count}}
                      <optgroup label="{{tr}}CConsultation-Office price{{/tr}}">
                        {{foreach from=$tarifs.func item=_tarif}}
                          <option value="{{$_tarif->_id}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                        {{/foreach}}
                      </optgroup>
                    {{/if}}
                    {{if "dPcabinet Tarifs show_tarifs_etab"|gconf && $tarifs.group|@count}}
                      <optgroup label="{{tr}}CConsultation-Etablishment price{{/tr}}">
                        {{foreach from=$tarifs.group item=_tarif}}
                          <option value="{{$_tarif->_id}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                        {{/foreach}}
                      </optgroup>
                    {{/if}}
                  </select>
                </td>
              </tr>
            {{else}}
              <tr>
                <th style="width: 225px;">{{mb_label object=$evenement field=tarif}}</th>
                <td>
                  {{if $evenement->valide}}
                    {{mb_script module=cabinet script=tarif ajax=true}}
                    <!-- Creation d'un nouveau tarif avec les actes de la consultation courante -->
                    <button id="inc_vw_reglement_button_create_tarif" class="submit" type="button" style="float: right;"
                            onclick="Tarif.newCodable('{{$evenement->_id}}', 'CConsultation', '{{$praticien->_id}}');">
                      {{tr}}CConsultation-action-new-tarif{{/tr}}
                    </button>
                  {{/if}}
                  {{mb_value object=$evenement field=tarif}}
                </td>
                {{if !$evenement->valide}}
                  <td class="button">
                    <button type="button" class="add" onclick="pursueTarif();">
                      {{tr}}Add{{/tr}}
                    </button>
                  </td>
                {{/if}}
              </tr>
            {{/if}}
          </table>
        </form>
        <!-- Fin formulaire de selection du tarif -->

        <!-- Formulaire date d'éxécution de tarif -->
        <table class="form me-small-form me-no-box-shadow">
          <tr>
            <th style="width: 225px;">{{mb_label object=$evenement field="exec_tarif"}}</th>
            <td>
              {{if $evenement->valide}}
                {{mb_value object=$evenement field="exec_tarif"}}
              {{else}}
                <form name="editExecTarif" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Facture.reloadEvt('{{$evenement->_guid}}', true);}});">
                  {{mb_key object=$evenement}}
                  {{mb_class object=$evenement}}
                  {{mb_field object=$evenement field="exec_tarif" form="editExecTarif" register=true onchange="this.form.onsubmit();"}}
                </form>
              {{/if}}
            </td>
          </tr>
        </table>

        <hr class="me-no-display" />

        <form name="tarifFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
          {{mb_key object=$evenement}}
          {{mb_class object=$evenement}}
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="_patient_id" value="{{$evenement->_ref_patient->_id}}" />

          <table style="width: 100%">
            <!-- Les actes codés -->
            {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
              <tr>
                <th class="me-text-align-right">{{tr}}CActeCCAM{{/tr}}</th>
                <td colspan="3">{{mb_field object=$evenement field="_tokens_ccam" readonly="readonly" hidden=1}}
                  {{foreach from=$evenement->_ref_actes_ccam item="acte_ccam"}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$acte_ccam->_guid}}');">{{$acte_ccam->_shortview}}</span>
                  {{/foreach}}
                </td>
              </tr>
              <tr>
                <th class="me-text-align-right">{{tr}}CActeNGAP{{/tr}}</th>
                <td colspan="3">{{mb_field object=$evenement field="_tokens_ngap" readonly="readonly" hidden=1}}
                  {{foreach from=$evenement->_ref_actes_ngap item=acte_ngap}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$acte_ngap->_guid}}');">{{$acte_ngap->_shortview}}</span>
                  {{/foreach}}
                </td>
              </tr>

              {{if "dPccam frais_divers use_frais_divers_CEvenementPatient"|gconf}}
                <tr>
                  <th>{{tr}}CFraisDivers{{/tr}}</th>
                  <td>
                    {{foreach from=$evenement->_ref_frais_divers item=frais}}
                      <span onmouseover="ObjectTooltip.createEx(this, '{{$frais->_guid}}');">{{$frais->_shortview}}</span>
                    {{/foreach}}
                  </td>
                </tr>
                </div>
              {{/if}}
            {{/if}}

            {{if $evenement->tarif && $evenement->valide}}
              <tr>
                <td colspan="4" class="button">
                  <input type="hidden" name="valide" value="1" />
                  {{if !$facture->_ref_reglements|@count}}
                    <button class="cancel" type="button" id="buttonCheckActe" onclick="checkActe();">
                      {{tr}}CConsultation-action-Reopen the quotation{{/tr}}
                    </button>
                  {{/if}}
                </td>
              </tr>
            {{elseif !$evenement->valide}}
              <tr>
                <td colspan="4" class="button">
                  <input type="hidden" name="_delete_actes" value="0" />
                  <input type="hidden" name="valide" value="1" />
                  {{mb_field object=$evenement field=tarif hidden=1}}
                  <button id="reglements_button_cloturer_cotation" class="submit" type="button" onclick="validTarif();">{{tr}}CConsultation-action-close-cotation{{/tr}}</button>
                  <button class="cancel" type="button" onclick="cancelTarif('delActes')">{{tr}}CConsultation-action-empty-cotation{{/tr}}</button>
                </td>
              </tr>
            {{/if}}
          </table>
        </form>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td id="load_facture" colspan="2">
      {{if $facture->_id || $facture->_ref_reglements|@count}}
        {{mb_include module=facturation template="inc_vw_facturation"}}
      {{/if}}
    </td>
  </tr>
  {{if $evenement->_ref_factures|@count > 1}}
    <tr>
      <td colspan="2">
        {{foreach from=$evenement->_ref_factures item=_facture}}
          {{if $_facture->numero != 1}}
            <button type="button" class="search" onclick="Facture.edit('{{$_facture->_id}}', '{{$_facture->_class}}');">
              {{tr var1=$_facture->numero}}CFactureEtablissement-Bill number %s-court{{/tr}}
            </button>
          {{/if}}
        {{/foreach}}
      </td>
    </tr>
  {{/if}}
</table>
