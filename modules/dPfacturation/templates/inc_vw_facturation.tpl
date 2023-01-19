{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture ajax=true}}
{{mb_script module=facturation script=tools ajax=true}}
{{mb_script module=cabinet script=edit_consultation ajax=true}}
{{if "dPfacturation CRelance use_relances"|gconf}}
    {{mb_script module=facturation script=relance ajax="true"}}
{{/if}}

<div id="reload-{{$facture->_guid}}">
    {{assign var="view" value="true"}}
    {{if "dPfacturation CRelance use_relances"|gconf || "dPfacturation CReglement use_echeancier"|gconf}}
        <script>
            Main.add(Control.Tabs.create.curry('tabs-configure-{{$facture->_guid}}', true));
        </script>
        {{assign var="view" value="false"}}
        <ul id="tabs-configure-{{$facture->_guid}}" class="control_tabs">
            <li><a href="#gestion_facture-{{$facture->_guid}}">{{tr}}CFacture-part-Invoice management{{/tr}}</a></li>
            <li><a
                  href="#reglements_facture-{{$facture->_guid}}">{{tr}}CFactureEtablissement-_reglements_total{{/tr}}</a>
            </li>
            {{if "dPfacturation CRelance use_relances"|gconf}}
                <li><a href="#relances-{{$facture->_guid}}">{{tr}}mod-dPfacturation-tab-ajax_vw_relances{{/tr}}</a></li>
            {{/if}}
            {{if "dPfacturation CReglement use_echeancier"|gconf}}
                <li><a href="#echeances-{{$facture->_guid}}">{{tr}}mod-dPfacturation-tab-vw_echeancier{{/tr}}</a></li>
            {{/if}}
        </ul>
    {{/if}}

    <div id="gestion_facture-{{$facture->_guid}}" {{if !$view}}style="display: none;"{{/if}} class="me-padding-0">
        <!-- Facture -->
        <fieldset class="hatching me-no-align me-no-box-shadow">
            {{if $facture && $facture->_id}}
                <legend>
                    {{mb_include module=system template=inc_object_notes object=$facture}}
                    {{tr}}{{$facture->_class}}{{/tr}}:
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_guid}}')">
            {{$facture}} {{if $facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}} n°{{$facture->_current_fse_number}}){{/if}}
          </span>
                </legend>
                <table class="main tbl me-no-box-shadow me-margin-top-0 me-no-hover">
                    {{assign var=use_autocloture value="dPfacturation `$facture->_class` use_auto_cloture"|gconf}}
                    {{if $facture->annule || ($facture->cloture && !$use_autocloture || $facture->extourne_id)}}
                        <tr>
                            <td colspan="7">
                                {{if $facture->extourne_id}}
                                    <div class="small-warning">
                                        {{tr}}CFacture-msg_extourne{{/tr}}:
                                        <span
                                          onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_extourne->_guid}}')">
                      {{$facture->_ref_extourne->_view}}
                    </span>
                                    </div>
                                {{/if}}
                                {{if $facture->annule}}
                                    <div class="small-warning">
                                        <strong>
                                            {{if $facture->extourne}}
                                                {{tr}}CFacture-msg-extournee{{/tr}}
                                                {{if $facture->_ref_new_facture}}
                                                    {{tr}}CFacture-_ref_new_facture{{/tr}}:
                                                    <span
                                                      onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_new_facture->_guid}}')">
                            {{$facture->_ref_new_facture->_view}}
                          </span>
                                                {{/if}}
                                            {{else}}
                                                {{tr}}CFacture-msg-annule{{/tr}}
                                            {{/if}}
                                        </strong>
                                    </div>
                                {{elseif ($facture->cloture && !$use_autocloture)}}
                                    <div class="small-info">
                                        {{tr}}CFacture-msg-cloture{{/tr}}
                                    </div>
                                {{/if}}
                            </td>
                        </tr>
                    {{/if}}
                    <tr>
                        <td style="text-align:center;width: 40%;" colspan="3">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_patient->_guid}}')">
                {{tr}}CFactureCabinet-patient_id{{/tr}} : {{$facture->_ref_patient}}
              </span>
                        </td>
                        <td style="text-align:center;width: 40%" colspan="3">
                            {{assign var=use_category_bill value="dPfacturation CFactureCategory use_category_bill"|conf:"CFunctions-`$facture->_ref_praticien->function_id`"}}
                            {{if $use_category_bill != "hide"}}
                                <span class="circled" style="float: right;"
                                      onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_category->_guid}}');">
                  {{$facture->_ref_category->_view}}
                </span>
                            {{/if}}
                            <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_praticien->_guid}}')">
                {{tr}}common-Practitioner{{/tr}}: {{$facture->_ref_praticien}}
              </span>
                        </td>
                        <td>
                            {{if $app->user_prefs.UISTYLE == "tamm" || $app->user_prefs.UISTYLE == "pluus"}}
                                <script>
                                    Main.add(function () {
                                        Consultation.moduleConsult = "oxCabinet";
                                    });
                                </script>
                            {{/if}}
                            {{tr}}CConsultation-msg-facture-liee{{/tr}}:
                            <ul>
                                {{foreach from=$facture->_ref_consults item=_consult}}
                                    <li>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
                      <button type="button" class="edit notext me-tertiary"
                              onclick="Consultation.editModal('{{$_consult->_id}}', 'reglement', null, function() {Facture.reloadFactureModal('{{$facture->_id}}', '{{$facture->_class}}');});">
                        {{tr}}CConsultation{{/tr}}
                      </button>
                      {{tr}}CConsultation-consult-on{{/tr}} {{mb_value object=$_consult->_ref_plageconsult field=date}}
                    </span>
                                        {{assign var=cons_class value="lock"}}
                                        {{assign var=cons_title value="is-validate"}}
                                        {{if !$_consult->valide}}
                                            {{assign var=cons_class value="lock-open"}}
                                            {{assign var=cons_title value="is-not-validate"}}
                                        {{/if}}
                                        <i class="fas fa-{{$cons_class}}"
                                           title="{{tr}}CFacture-consultation-{{$cons_title}}{{/tr}}"></i>
                                        {{if !$facture->cloture &&
                                        ($app->_ref_user->function_id === $facture->_ref_praticien->function_id || $can->admin)}}
                                            <button type="button" class="unlink notext me-tertiary"
                                                    onclick="FactuTools.FactuLiaisonManager.objectFastUnlink('{{$_consult->_guid}}')">
                                                {{tr}}CFactureLiaison.Manager open link manager{{/tr}}
                                            </button>
                                        {{/if}}
                                    </li>
                                {{/foreach}}
                                {{foreach from=$facture->_ref_sejours item=_sejour}}
                                    <li>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                      {{tr}}CSejour{{/tr}} {{$_sejour->_shortview}}
                    </span>
                                    </li>
                                {{/foreach}}
                                {{foreach from=$facture->_ref_evts item=_evt}}
                                    <li>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_evt->_guid}}')">
                      {{tr}}CEvent{{/tr}} {{$_evt->_view}} le {{mb_value object=$_evt field=date}}
                    </span>
                                        {{if !$facture->cloture &&
                                        ($app->_ref_user->function_id === $facture->_ref_praticien->function_id || $can->admin)}}
                                            <button type="button" class="unlink notext me-tertiary"
                                                    onclick="FactuTools.FactuLiaisonManager.objectFastUnlink('{{$_evt->_guid}}')">
                                                {{tr}}CFactureLiaison.Manager open link manager{{/tr}}
                                            </button>
                                        {{/if}}
                                    </li>
                                {{/foreach}}
                            </ul>
                        </td>
                    </tr>
                </table>
                <table class="main tbl">
                    {{mb_include module=dPfacturation template=inc_vw_facturation_t2a}}
                </table>
            {{else}}
                <legend class="empty">{{tr}}CFactureCabinet.none{{/tr}}</legend>
            {{/if}}
        </fieldset>
    </div>

    <!-- Relances -->
    {{if "dPfacturation CRelance use_relances"|gconf}}
        <div id="relances-{{$facture->_guid}}" {{if !$view}}style="display: none;"{{/if}} class="me-padding-0">
            {{if $facture->_ref_relances|@count}}
                {{mb_include module=dPfacturation template="inc_vw_relances"}}
            {{else}}
                <div class="small-info">{{tr}}CFacture-msg-relance-none{{/tr}}</div>
            {{/if}}
        </div>
    {{/if}}

    <!-- Reglements -->
    <div id="reglements_facture-{{$facture->_guid}}" {{if !$view}}style="display: none;"{{/if}} class="me-padding-0">
        {{if $facture->_id && !$facture->annule && ($facture->cloture || "dPfacturation CReglement add_pay_not_close"|gconf) && (!isset($show_button|smarty:nodefaults) || $show_button)}}
            {{mb_include module=dPfacturation template="inc_vw_reglements"}}
        {{elseif $facture->_id}}
            <div class="small-info">{{tr}}CFacture-msg-close-invoice{{/tr}}</div>
            {{if $facture->_reglements_total_patient}}
                {{mb_include module=dPfacturation template="inc_vw_reglements" can_add=false}}
            {{/if}}
        {{/if}}
    </div>

    {{if "dPfacturation CReglement use_echeancier"|gconf}}
        <!-- Echelonnements -->
        <div id="echeances-{{$facture->_guid}}" {{if !$view}}style="display: none;"{{/if}}>
            {{mb_script module=facturation script=echeance ajax=true}}
            <script>
                Main.add(function () {
                    Echeance.loadList('{{$facture->_id}}', '{{$facture->_class}}');
                });
            </script>
        </div>
    {{/if}}
</div>
