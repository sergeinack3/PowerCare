{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
    <th colspan="11">
        <div class="me-display-flex me-justify-content-space-between">
            <div class="me-inline">
                <span>
                    <form name="ConsultationTarifSelection" method="post" action="?m" onsubmit="return onSubmitFormAjax(this, Invoicing.reload.bind(Invoicing, '{{$consultation->_id}}', '{{$invoice->id}}'))">
                        <input type="hidden" name="m" value="cabinet" />
                        <input type="hidden" name="del" value="0" />
                        <input type="hidden" name="dosql" value="do_consultation_aed" />
                        {{mb_key object=$consultation}}
                        <input type="hidden" name="_bind_tarif" value="1" />
                        <input type="hidden" name="_delete_actes" value="0" />

                        <label title="{{tr}}CConsultation-cotation-desc{{/tr}}">
                            {{tr}}CConsultation-cotation{{/tr}}
                            <select name="_codable_guid"  class="str" style="width: 130px;" onchange="this.form.onsubmit();"{{if $consultation->valide === '1'}} disabled="disabled"{{/if}}>
                              <option value="" selected="selected">&mdash; {{tr}}Choose{{/tr}}</option>
                              {{if $tarifs.user|@count}}
                                <optgroup label="{{tr}}CConsultation-Practitioner price{{/tr}}">
                                {{foreach from=$tarifs.user item=_tarif}}
                                  <option value="{{$_tarif->_guid}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                {{/foreach}}
                                </optgroup>
                              {{/if}}
                              {{if $tarifs.func|@count}}
                                <optgroup label="{{tr}}CConsultation-Office price{{/tr}}">
                                {{foreach from=$tarifs.func item=_tarif}}
                                  <option value="{{$_tarif->_guid}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                {{/foreach}}
                                </optgroup>
                              {{/if}}
                              {{if "dPcabinet Tarifs show_tarifs_etab"|gconf && $tarifs.group|@count}}
                                <optgroup label="{{tr}}CConsultation-Etablishment price{{/tr}}">
                                {{foreach from=$tarifs.group item=_tarif}}
                                  <option value="{{$_tarif->_guid}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                {{/foreach}}
                                </optgroup>
                              {{/if}}
                              {{if is_array($list_devis) && $list_devis|@count}}
                                  <optgroup label="{{tr}}CDevisCodage{{/tr}}">
                                    {{foreach from=$list_devis item=_devis}}
                                      <option value="{{$_devis->_guid}}">{{$_devis->libelle}}</option>
                                    {{/foreach}}
                                  </optgroup>
                              {{/if}}
                            </select>
                        </label>
                    </form>
                </span>
                <span>
                    <form name="editExecTarif" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
                        {{mb_key object=$consultation}}
                        {{mb_class object=$consultation}}
                        {{mb_label object=$consultation field="exec_tarif"}}
                        {{mb_field object=$consultation field="exec_tarif" form="editExecTarif" register=true onchange="this.form.onsubmit();"}}
                      </form>
                </span>
            </div>
            {{if $invoice->user_interface->display_treatment_type || $invoice->user_interface->amendment_27_consultation_help}}
              <div class="me-inline" style="width: 100%; text-align: right;">
                  {{if $invoice->user_interface->amendment_27_consultation_help}}
                      <button type="button" class="fas fa-child notext" onclick="Invoicing.displayChildrenConsultationAssistant('{{$invoice->id}}');">
                          {{tr}}CJfseChildrenConsultationAssistant-action{{/tr}}
                      </button>
                  {{/if}}
                  {{if $invoice->user_interface->display_treatment_type}}
                      <form name="setTreatmentType" method="POST" action="?" onsubmit="return Invoicing.setTreatmentType(this);">
                          <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                          <input type="radio" name="treatment_type" value="0" style="display: none;"{{if $invoice->treatment_type === '0'}} checked="checked"{{/if}}>
                          <label>
                              <input type="radio" name="treatment_type" value="1"{{if $invoice->treatment_type === '1'}} checked="checked"{{/if}} onclick="this.form.onsubmit();">
                              {{tr}}CJfseInvoiceView.treatment_type.1{{/tr}}
                          </label>
                          <label>
                              <input type="radio" name="treatment_type" value="2"{{if $invoice->treatment_type === '2'}} checked="checked"{{/if}} onclick="this.form.onsubmit();">
                              {{tr}}CJfseInvoiceView.treatment_type.2{{/tr}}
                          </label>
                      </form>
                  {{/if}}
              </div>
            {{/if}}
        </div>
    </th>
</tr>
