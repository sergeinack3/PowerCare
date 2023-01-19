{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=protocole_selector}}
{{mb_script module=planningOp script=sejour_multiple}}

{{assign var=dhe_urgences_lite value="dPplanningOp CSejour dhe_urgences_lite"|gconf}}
{{if $dhe_urgences_lite && $mutation}}
    {{assign var=mutation value=1}}
{{else}}
    {{assign var=mutation value=0}}
{{/if}}

<script>
    ProtocoleSelector.init = function (do_not_pop) {
        this.sForSejour = true;
        this.sForm = "editSejour";
        this.sChir_id = "praticien_id";
        this.sChir_view = "praticien_id_view";
        this.sServiceId = "service_id";
        this.sDP = "DP";
        this.sDR = 'DR';
        this.sDepassement = "depassement";

        this.sLibelle_sejour = "libelle";
        this.sType = "type";
        this.sCharge_id = "charge_id";
        this.sDuree_prevu = "_duree_prevue";
        this.sDuree_prevu_heure = "_duree_prevue_heure";
        this.sConvalescence = "convalescence";
        this.sRques_sej = "rques";
        this.sUf_hebergement_id = "uf_hebergement_id";
        this.sUf_medicale_id = "uf_medicale_id";
        this.sUf_soins_id = "uf_soins_id";

        {{if "hidden" !== "dPplanningOp CSejour fields_display show_type_pec"|gconf}}
        this.sTypePec = "type_pec";
        {{/if}}

        {{if "dPplanningOp CSejour fields_display show_facturable"|gconf}}
        this.sFacturable = "facturable";
        {{/if}}

        this.sHospitDeJour = "hospit_de_jour";

        this.sProtoPrescAnesth = "_protocole_prescription_anesth_id";
        this.sProtoPrescChir = "_protocole_prescription_chir_id";
        this._sProtocole_id = "_protocole_id";
        this.sRRAC = "RRAC";
        this.sCodage_NGAP_sejour = '_codage_ngap';

        this.sPack_appFine_ids = "_pack_appFine_ids";
        this.sDocItems_guid_sejour = "_docitems_guid";

        this.sHour_entree_prevue = "_hour_entree_prevue";
        this.sMin_entree_prevue = "_min_entree_prevue";
        this.sCircuit_ambu = "circuit_ambu";

        if (!do_not_pop) {
            this.pop();
        }
    };

    {{if "appFineClient"|module_active}}
    synchronizeTypesPacksAppFine = function (types) {
        // Réinitialisation des packs du protocole
        window.packs_non_stored = [];
        window.packs_non_stored = types.split(",");
    };

    addPacksAppFine = function (pack_ids) {
        var form = getForm("addPackProtocole");
        pack_ids = pack_ids.split(",");

        pack_ids.each(function (pack_id) {
            $V(form.pack_id, pack_id);
            onSubmitFormAjax(form);
        });
    };
    {{/if}}

    function toggleMode() {
        var trigger = $("modeExpert-trigger"),
            hiddenElements = $$(".modeExpert"),
            expert = !hiddenElements[0].visible();
        trigger.update($T("button-COperation-mode" + (expert ? "Expert" : "Easy")));
        hiddenElements.invoke("setVisible", expert);
    }

    {{if $sejour->_id && $dialog == 1 && !$ext_cabinet_id}}
    // Il faut sauvegarder le sejour_id pour la création de l'affectation
    // après la fermeture de la modale.
    window.parent.sejour_id_for_affectation = '{{$sejour->_id}}';
    {{/if}}

    function addFastOperation(button, operation_id) {
        new Url("planningOp", "ajax_add_fast_operation")
            .addParam("sejour_id", "{{$sejour->_id}}")
            .addParam("operation_id", operation_id)
            .addParam("praticien_id", $V(getForm("editSejour").praticien_id))
            .requestModal("50%", "50%", {
                onClose: function () {
                    refreshListOps();
                }
            });
    }

    function refreshListOps() {
        new Url("planningOp", "ajax_list_fast_operations")
            .addParam("sejour_id", "{{$sejour->_id}}")
            .requestUpdate("list_operations");
    }

    showLegende = function () {
        new Url('planningOp', 'vw_legende').requestModal(250, 250);
    };

    Main.add(function () {
        window.packs_non_stored = [];

        reloadSejours(1, 100);

        if (Preferences.mode_dhe === "0") {
            toggleMode();
        }

        {{if $mutation || $dhe_mater}}
        refreshListOps();
        {{/if}}
    });
</script>

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    <form name="addPackProtocole" method="post">
        <input type="hidden" name="m" value="appFineClient"/>
        <input type="hidden" name="dosql" value="do_pack_protocole_aed"/>
        <input type="hidden" name="pack_id"/>
        <input type="hidden" name="sejour_id" value="{{$sejour->_id}}"/>
    </form>
{{/if}}

<table class="main">
    {{if $sejour->_id && !$dialog}}
        <tr>
            <td colspan="2">
                <a id="didac_program_new_sejour" class="button new me-primary"
                   href="?m={{$m}}&tab={{$tab}}&sejour_id=0">
                    {{tr}}CSejour.create{{/tr}}
                </a>
            </td>
        </tr>
    {{/if}}
    <tr>
        <th colspan="2" class="title {{if $sejour->_id}}modify{{/if}}">
            {{if $sejour->_id}}
                {{mb_include module=system template=inc_object_idsante400 object=$sejour}}
                {{mb_include module=system template=inc_object_history    object=$sejour}}
                {{mb_include module=system template=inc_object_notes      object=$sejour}}
            {{/if}}

            <button type="button" class="search" style="float: left;" onclick="ProtocoleSelector.init()">
                {{tr}}button-COperation-choixProtocole{{/tr}}
            </button>

            <button type="button" class="hslip" style="float: right;" onclick="toggleMode(this)"
                    id="modeExpert-trigger">
                {{tr}}button-COperation-modeExpert{{/tr}}
            </button>

            {{if $sejour->_id}}
                Modification du séjour {{$sejour}}
                {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
            {{else}}
                Création d'un nouveau séjour

                {{if !$contextual_call}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient}}</span>
                {{else}}
                    {{$patient}}
                {{/if}}

            {{/if}}

            {{mb_include module=patients template=inc_icon_bmr_bhre}}

            {{if $sejour->presence_confidentielle}}
                {{mb_include module=planningOp template=inc_badge_sejour_conf}}
            {{/if}}
        </th>
    </tr>
    {{if $patient->deces}}
        <tr>
            <td>
                <div class="small-warning">{{tr}}CSejour-info-patient-deceased{{/tr}}</div>
            </td>
        </tr>
    {{/if}}
    <tr>
        <td colspan="2" {{if !$sejour->_alertes_ufs|@count}}style="display: none;"{{/if}}>
            {{mb_include module=hospi template=inc_alerte_ufs object=$sejour}}
        </td>
    </tr>

    <tr>
        <td style="width: 60%">
            {{mb_include module=planningOp template=js_form_sejour}}
            {{mb_include module=planningOp template=inc_form_sejour mode_operation=false protocole_autocomplete=true}}
        </td>
        {{if !$mutation}}
            <td>
                {{if $m != "reservation" && $sejour->_id}}
                    <a class="button new me-primary"
                       href="?m={{$m}}&tab=vw_edit_planning&operation_id=0&sejour_id={{$sejour->_id}}"
                       id="link_operation" target="_parent">
                        Programmer une nouvelle intervention dans ce séjour
                    </a>
                    <label>
                        <input type="checkbox" onclick="
                          if (this.checked) {
                          $('link_operation').href = '?m={{$m}}&tab=vw_edit_urgence&operation_id=0&sejour_id={{$sejour->_id}}';
                          }
                          else {
                          $('link_operation').href = '?m={{$m}}&tab=vw_edit_planning&operation_id=0&sejour_id={{$sejour->_id}}';
                          }"/> Hors plage
                    </label>
                {{/if}}
                {{mb_include module=planningOp template=inc_infos_operation}}
                {{mb_include module=planningOp template=inc_infos_hospitalisation}}
                <table class="form" style="width: 100%;">
                    <tr>
                        <th class="title">
                            {{tr}}CSejour-existants{{/tr}}
                            <button type="button" class="search" style="float: right;"
                                    onclick="showLegende();">{{tr}}Legend{{/tr}}</button>
                        </th>
                    </tr>
                    <tr>
                        <td id="list_sejours"></td>
                    </tr>

                    {{if $sejour->_id}}
                        <tr>
                            <td>
                                {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$sejour->patient_id object=$sejour show_send_mail=1}}
                            </td>
                        </tr>
                        <tr>
                            <th class="title">
                                {{tr}}CMbObject-back-documents{{/tr}}
                            </th>
                        </tr>
                        <tr>
                            <td id="documents">
                                {{mb_script module=compteRendu script=document}}
                                {{mb_script module=compteRendu script=modele_selector}}
                                <script>
                                    Document.register('{{$sejour->_id}}', '{{$sejour->_class}}', '{{$sejour->praticien_id}}', 'documents', null, {ext_cabinet_id: '{{$ext_cabinet_id}}'});
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <th class="title">
                                {{tr}}CMbObject-back-files{{/tr}}
                            </th>
                        </tr>
                        <tr>
                            <td id="files">
                                {{mb_script module=files script=file}}
                                <script>
                                    File.register('{{$sejour->_id}}', '{{$sejour->_class}}', 'files', undefined, null, {ext_cabinet_id: '{{$ext_cabinet_id}}'});
                                </script>
                            </td>
                        </tr>
                    {{/if}}

                    {{if $sejour->_id && "forms"|module_active}}
                        <tr>
                            <th class="title">
                                {{tr}}CMbObject-back-ex_links_meta{{/tr}}
                            </th>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                {{unique_id var=unique_id_sejour_forms}}

                                <script>
                                    Main.add(function () {
                                        ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "{{$unique_id_sejour_forms}}", 0.5);
                                    });
                                </script>

                                <div id="{{$unique_id_sejour_forms}}"></div>
                            </td>
                        </tr>
                    {{/if}}
                </table>
            </td>
        {{/if}}
    </tr>
</table>

<div id="list_operations"></div>
