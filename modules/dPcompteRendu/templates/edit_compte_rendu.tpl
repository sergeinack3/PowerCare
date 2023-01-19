{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=board script=board}}
{{if $compte_rendu->_is_dompdf}}
    <div class="small-warning">
        {{tr}}CCompteRendu-Alert on dompdf doc{{/tr}}
    </div>
{{/if}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}
{{assign var=header_footer_fly value="dPcompteRendu CCompteRendu header_footer_fly"|gconf}}
{{assign var=time_autosave value=$app->user_prefs.time_autosave}}

{{mb_default var=unique_id value=null}}

{{mb_script module=compteRendu script=thumb}}
{{mb_script module=compteRendu script=compte_rendu}}
{{mb_script module=compteRendu script=read_write_timer}}
{{if $pdf_and_thumbs}}
    {{mb_script module=compteRendu script=layout}}
{{/if}}
{{mb_script module=files script=file_category}}
{{if 'apicrypt'|module_active}}
    {{mb_script module=apicrypt script=Apicrypt}}
{{/if}}

<script>
    window.same_print = {{"dPcompteRendu CCompteRenduPrint same_print"|gconf}};
    window.nb_printers = {{if $nb_printers}}{{$nb_printers|intval}}{{else}}0{{/if}};
    window.modal_mode_play = null;
    window.documentGraphs = {{$templateManager->graphs|@json}};
    window.saving_doc = false;
    document.title = '{{$compte_rendu->_ref_object->_view|smarty:nodefaults|JSAttribute}} - {{$compte_rendu->nom|smarty:nodefaults|JSAttribute}}';

    function openWindowMail() {
        {{if $exchange_source->_id}}
        var form = getForm("editFrm");
        var url = new Url("compteRendu", "ajax_view_mail");
        url.addParam("object_guid", "CCompteRendu-" + $V(form.compte_rendu_id));
        url.requestModal(700, '90%');
        {{else}}
        alert("Veuillez paramétrer votre compte mail (source smtp dans les préférences utilisateur).");
        {{/if}}
    }

    function openWindowApicrypt() {
        var form = getForm("editFrm");
        var url = new Url("apicrypt", "ajax_view_apicrypt_mail");
        url.addParam("object_guids[]", ['{{$compte_rendu->object_class}}-{{$compte_rendu->object_id}}'], true);
        url.addParam("doc_guids[]", ['CCompteRendu-'+$V(form.compte_rendu_id)], true);
        url.requestModal(750, 600);
    }

    function openWindowMSSante() {
        var url = new Url('mssante', 'viewSendDocument');
        url.addParam("object_guid", '{{$compte_rendu->object_class}}-{{$compte_rendu->object_id}}');
        url.addParam("document_guid", '{{$compte_rendu->_guid}}');
        url.requestModal(700, 600);
    }

    function openWindowMedimail(ihe_xdm) {
        var form = getForm('editFrm');
        var url = new Url('medimail', 'editMessage');
        url.addParam('action', 'send_document');
        url.addParam('object_class', 'CCompteRendu');
        url.addParam('object_id', $V(form.compte_rendu_id));
        url.addParam('ihe_xdm', ihe_xdm);
        url.requestModal(700, 600);
    }

    function openModalPrinters() {
        // Mise à jour de la date d'impression
        $V(getForm("editFrm").date_print, "now");

        // Si une imprimante est déjà paramétrée, on peut donc déjà lancer l'impression
        {{if $compte_rendu->printer_id}}
        printServer('{{$compte_rendu->_ref_printer->object_class}}-{{$compte_rendu->_ref_printer->object_id}}');
        {{else}}
        window.modalPrinters = new Url("compteRendu", "ajax_choose_printer");
        modalPrinters.requestModal(700, 400, {showReload: false});
        {{/if}}
    }

    function refreshListDocs() {
        var form = getForm("editFrm");
        if (window.opener.Document && window.opener.Document.refreshList) {
            window.opener.Document.refreshList($V(form.file_category_id), $V(form.object_class), $V(form.object_id), null, window.unique_id_refresh);
        }
        if (window.opener.reloadListFile) {
            var categorie_id = $V(form.file_category_id);
            window.opener.reloadListFile(null, categorie_id ? categorie_id : 0);
        }
        if (window.opener.TdBTamm) {
            window.opener.TdBTamm.refreshTimeline(window.opener.TdBTamm.patient_id);
        }
    }

    function printDocs(compte_rendu_id, quantity) {
        var url = new Url("compteRendu", "print_docs", "raw");
        url.addParam("nbDoc[" + compte_rendu_id + "]", quantity);
        url.popup();
    }

    Main.add(function () {
        Thumb.instance = CKEDITOR.instances.htmlarea;

        window.unique_id_refresh = '{{$unique_id}}';
        setStatutDisabled($('statut_compte_rendu'),'{{$app->_ref_user->isSecretaire()}}');

        {{if $read_only}}
        Thumb.doc_lock = true;
        {{/if}}

        if (window.Preferences.pdf_and_thumbs == 1) {
            PageFormat.init(getForm("editFrm"));
            Thumb.compte_rendu_id = '{{$compte_rendu->_id}}';
            Thumb.modele_id = '{{$modele_id}}';
            Thumb.user_id = '{{$user_id}}';
            Thumb.mode = "doc";
            Thumb.object_class = '{{$compte_rendu->object_class}}';
            Thumb.object_id = '{{$compte_rendu->object_id}}';

            PageFormat.form.factory.down('option[value="CDomPDFConverter"]').disabled = true;
        }

        // Les correspondants doivent être présent pour le store du compte-rendu
        // Chargement en arrière-plan de la modale
        {{if $isCourrier && !$compte_rendu->valide}}
        openCorrespondants('{{$compte_rendu->_id}}', '{{$compte_rendu->_ref_object->_guid}}', 0);
        {{/if}}

        if (window.opener.Document && Object.isFunction(window.opener.Document.documentClose)) {
            window.addEventListener("beforeunload", window.opener.Document.documentClose);
        }

        window.onbeforeunload = function (e) {
            e = e || window.event;

            if (Thumb.contentChanged == false) {
                return;
            }

            if (window.Preferences.pdf_and_thumbs == 1 && Thumb.contentChanged == true) {
                emptyPDF();
            }

            if (e) {
                e.returnValue = ' ';
            }

            return ' ';
        };

        var htmlarea = $('htmlarea');

        // documentGraphs est un tableau si vide ($H donnera les mauvaises clés), un objet sinon
        if (documentGraphs.length !== 0) {
            $H(documentGraphs).each(function (pair) {
                var g = pair.value;
                $('graph-container').update();
                g.options.fontSize = 14;
                g.options.resolution = 2;
                g.options.legend = {
                    labelBoxWidth: 28,
                    labelBoxHeight: 20
                };
                g.options.pie.explode = 0;
                var f = new Flotr.Graph($('graph-container'), g.data, g.options);
                g.dataURL = f.canvas.toDataURL();
                oFCKeditor.value = htmlarea.value = htmlarea.value.replace('<' + 'span class="field">' + g.name + '</' + 'span>', '<' + 'img src="' + g.dataURL + '" width="450" height="300" /' + '>');
            });
        }

        {{if !$compte_rendu->_id && $switch_mode == 1}}
        if (window.opener.saveFields) {
            from = window.opener.saveFields;
            var to = getForm("editFrm");
            if (from[0].any(function (elt) {
                return elt.size > 1;
            })) {
                toggleOptions();
            }
            from.each(function (elt) {
                elt.each(function (select) {
                    if (select) {
                        $V(to[select.name], $V(select));
                    }
                })
            });
        }
        {{/if}}

        refreshListDocs();

        ObjectTooltip.modes.locker = {
            module: "compteRendu",
            action: "ajax_show_locker",
            sClass: "tooltip"
        };

        var form = getForm("LockDocOther");
        var url = new Url("mediusers", "ajax_users_autocomplete");
        url.addParam("input_field", form._user_view.name);
        url.autoComplete(form._user_view, null, {
            minChars: 0,
            method: "get",
            select: "view",
            dropdown: true,
            width: '200px',
            afterUpdateElement: function (field, selected) {
                $V(form._user_view, selected.down('.view').innerHTML);
                var id = selected.getAttribute("id").split("-")[2];
                $V(form.user_id, id);
            }
        });

        {{if $compte_rendu->_sent_mail || $compte_rendu->_sent_apicrypt || $compte_rendu->_sent_mssante}}
        CKEDITOR.on('instanceReady', function () {
            {{if $compte_rendu->_sent_mail}}
            CKEDITOR.instances.htmlarea.execCommand('usermessage_toggle_icon');
            {{/if}}
            {{if $compte_rendu->_sent_apicrypt}}
            CKEDITOR.instances.htmlarea.execCommand('apicrypt_toggle_icon');
            {{/if}}
            {{if $compte_rendu->_sent_mssante}}
            CKEDITOR.instances.htmlarea.execCommand('mssante_toggle_icon');
            {{/if}}
        });
        {{/if}}

        {{if $compte_rendu->_id && $time_autosave}}
        // Sauvegarde automatique
        new PeriodicalExecuter(function () {
            submitCompteRendu();
        }, {{$time_autosave}});
        {{/if}}

        {{if $user_opener->_id}}
        setTimeout(function () {
            $('opener-tooltip').addClassName('displayed');
        }, 1000);
        {{/if}}

        ReadWriteTimer.init(
          '{{$compte_rendu->_id}}',
          {{if $creation && $compte_rendu->modele_id}}'{{$compte_rendu->modele_id}}'{{else}}''{{/if}},
          {{if $creation}}true{{else}}false{{/if}}
        );
    });
</script>

<div style="position: absolute; top: -1500px;">
    <div style="position: relative; width: 900px; height: 600px;" id="graph-container"></div>
</div>

<!-- Modale pour le mode play -->
<div style="display: none;" id="play_modal">
    <table class="form">
        <tr>
            <th class="title" style="cursor: move">
                {{tr}}CCompteRendu-mode_play{{/tr}}
            </th>
        </tr>
        <tr>
            <td class="field_aera" style="padding-top: 10px;">
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <button class="add" onclick="Modal.open('add_field_area');">{{tr}}Add{{/tr}}</button>
                <button class="tick">{{tr}}Apply{{/tr}}</button>
                <button class="trash">{{tr}}Empty{{/tr}}</button>
                <button class="cancel">{{tr}}Close{{/tr}}</button>
            </td>
        </tr>
    </table>
</div>

<div id="add_field_area" style="display: none;">
    <table class="form">
        <tr>
            <th class="title">
                {{tr}}CCompteRendu-Add choice field{{/tr}}
            </th>
        </tr>
        <tr>
            <td>
                <input type="text" style="width: 300px;"/>
            </td>
        </tr>
        <tr>
            <td class="button">
                <button class="tick">{{tr}}Validate{{/tr}}</button>
                <button class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
            </td>
        </tr>
    </table>
</div>

{{mb_include module=compteRendu template=inc_form_utils}}

<!-- Zone cachée pour la génération PDF et l'impression server side -->
<div id="pdf_area" style="display: none;"></div>

<!-- Zone de confirmation de verrouillage du document -->
{{mb_include module=compteRendu template=inc_area_lock}}

<iframe name="download_pdf" style="width: 500px; height: 500px; position: absolute; top: -1000px;"></iframe>

<form name="editFrm" action="?m={{$m}}" method="post"
      onsubmit="Url.ping(function() {
        // Flag pour lancer la modale d'action après fermeture de la popup
        if (Thumb.contentChanged) {
        window.contentChanged = true;
        }

        // Ne pas déclencher l'alerte de document modifié lorsque l'on est en train de sauvegarder
        Thumb.contentChanged = false;

      {{if !$compte_rendu->_id}}
        var editor = CKEDITOR.instances.htmlarea;
        var form = getForm('editFrm');
        var dests = $('destinataires');

        if (!checkForm(form)) {
          return false;
        }

        if (editor.getCommand('save')) {
          editor.getCommand('save').setState(CKEDITOR.TRISTATE_DISABLED);
        }
        if (dests && dests.select('input:checked').length) {
          $V(form.do_merge, 1);
        }
        if (Prototype.Browser.IE) {
          restoreStyle();
        }
        $V(form.duree_ecriture, ReadWriteTimer.getTime());
        form.submit();
      {{else}}
        submitCompteRendu();
      {{/if}}
        });
        return false;"
      class="{{$compte_rendu->_spec}}">
    <input type="hidden" name="m" value="compteRendu"/>
    <input type="hidden" name="dosql" value="do_modele_aed"/>
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="function_id" value=""/>
    <input type="hidden" name="user_id" value=""/>
    <input type="hidden" name="group_id" value=""/>
    <input type="hidden" name="unique_id" value="{{$unique_id}}"/>
    <input type="hidden" name="switch_mode" value='{{$switch_mode}}'/>
    <input type="hidden" name="date_print" value="{{$compte_rendu->date_print}}"/>
    <input type="hidden" name="do_merge" value="0"/>
    <input type="hidden" name="purge_field" value="{{$compte_rendu->purge_field}}"/>
    <input type="hidden" name="callback" value="callbackSave"/>

    {{mb_key object=$compte_rendu}}
    {{mb_field object=$compte_rendu field="object_id" hidden=1}}
    {{mb_field object=$compte_rendu field="object_class" hidden=1}}
    {{mb_field object=$compte_rendu field="modele_id" hidden=1}}
    {{mb_field object=$compte_rendu field="font" hidden=1}}
    {{mb_field object=$compte_rendu field="size" hidden=1}}
    {{mb_field object=$compte_rendu field="valide" hidden=1}}
    {{mb_field object=$compte_rendu field="locker_id" hidden=1}}
    {{mb_field object=$compte_rendu field="author_id" hidden=1}}
    {{mb_field object=$compte_rendu field="signataire_id" hidden=1}}
    {{mb_field object=$compte_rendu field="signature_mandatory" hidden=1}}
    {{mb_field object=$compte_rendu field="alert_creation" hidden=1}}
    {{mb_field object=$compte_rendu field="printer_id" hidden=1}}
    {{mb_field object=$compte_rendu field="duree_ecriture" hidden=1}}
    {{mb_field object=$compte_rendu field="_ext_cabinet_id" hidden=1}}
    {{mb_field object=$compte_rendu field=annule hidden=1}}

    {{if $header_footer_fly}}
        <div id="header_footer_fly" style="display: none">
            <table class="tbl">
                <tr>
                    <th>
                        {{mb_label object=$compte_rendu field=header_id}} :
                    </th>
                    {{if $headers|@count && ($headers.prat|@count > 0 || $headers.func|@count > 0 || $headers.etab|@count > 0)}}
                        <td>
                            <select name="header_id" onchange="Thumb.old();" class="{{$compte_rendu->_props.header_id}}"
                                    style="width: 15em;">
                                <option value="" {{if !$compte_rendu->header_id}}selected{{/if}}>
                                    &mdash; {{tr}}CCompteRendu-set-header{{/tr}}</option>
                                {{foreach from=$headers item=headersByOwner key=owner}}
                                    {{if $headersByOwner|@count}}
                                        <optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
                                            {{foreach from=$headersByOwner item=_header}}
                                                <option value="{{$_header->_id}}"
                                                        {{if $compte_rendu->header_id == $_header->_id}}selected{{/if}}>{{$_header->nom}}</option>
                                                {{foreachelse}}
                                                <option value="" disabled>{{tr}}None{{/tr}}</option>
                                            {{/foreach}}
                                        </optgroup>
                                    {{/if}}
                                {{/foreach}}
                                {{* Entête associé à un modèle provenant d'un autre type d'objet *}}
                                {{assign var=header_id value=$compte_rendu->header_id}}
                                {{if $compte_rendu->header_id && !isset($headers.prat.$header_id|smarty:nodefaults) && !isset($headers.func.$header_id|smarty:nodefaults) &&
                                !isset($headers.etab.$header_id|smarty:nodefaults)}}
                                    <option value="{{$compte_rendu->header_id}}"
                                            selected>{{$compte_rendu->_ref_header->nom}}</option>
                                {{/if}}
                            </select>
                        </td>
                    {{else}}
                        <td class="empty">
                            {{mb_field object=$compte_rendu field=header_id hidden=1}}
                            Pas d'entête
                        </td>
                    {{/if}}
                </tr>
                <tr>
                    <th>
                        {{mb_label object=$compte_rendu field=footer_id}} :
                    </th>
                    {{if $footers|@count && ($footers.prat|@count > 0 || $footers.func|@count > 0 || $footers.etab|@count > 0)}}
                        <td>
                            <select name="footer_id" onchange="Thumb.old();" class="{{$compte_rendu->_props.footer_id}}"
                                    style="width: 15em;">
                                <option value="" {{if !$compte_rendu->footer_id}}selected{{/if}}>
                                    &mdash; {{tr}}CCompteRendu-set-footer{{/tr}}</option>
                                {{foreach from=$footers item=footersByOwner key=owner}}
                                    {{if $footersByOwner|@count}}
                                        <optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
                                            {{foreach from=$footersByOwner item=_footer}}
                                                <option value="{{$_footer->_id}}"
                                                        {{if $compte_rendu->footer_id == $_footer->_id}}selected{{/if}}>{{$_footer->nom}}</option>
                                                {{foreachelse}}
                                                <option value="" disabled>{{tr}}None{{/tr}}</option>
                                            {{/foreach}}
                                        </optgroup>
                                    {{/if}}
                                {{/foreach}}
                                {{* Pied de page associé à un modèle provenant d'un autre type d'objet *}}
                                {{assign var=footer_id value=$compte_rendu->footer_id}}
                                {{if $compte_rendu->footer_id && !isset($footers.prat.$footer_id|smarty:nodefaults) && !isset($footers.func.$footer_id|smarty:nodefaults) &&
                                !isset($footers.etab.$footer_id|smarty:nodefaults)}}
                                    <option value="{{$compte_rendu->footer_id}}"
                                            selected>{{$compte_rendu->_ref_footer->nom}}</option>
                                {{/if}}
                            </select>
                        </td>
                    {{else}}
                        <td class="empty">
                            {{mb_field object=$compte_rendu field=footer_id hidden=1}}
                            Pas de pied de page
                        </td>
                    {{/if}}
                </tr>
                <tr>
                    <td class="button" colspan="2">
                        <button type="button" class="tick"
                                onclick="Control.Modal.close()">{{tr}}Validate{{/tr}}</button>
                        <button type="button" class="cancel" onclick="modalHeaderFooter(0);">{{tr}}Close{{/tr}}</button>
                    </td>
                </tr>
            </table>
        </div>
    {{/if}}

    <table class="form">
        <tr>
            <th class="category narrow" style="vertical-align: top;">
                {{if $compte_rendu->_id}}
                    <a class="button left {{if !$prevnext.prev}}disabled{{/if}}"
                            {{if $prevnext.prev}}
                        href="?m=compteRendu&dialog=edit&compte_rendu_id={{$prevnext.prev}}"
                            {{/if}}>
                        Préc.
                    </a>
                {{/if}}
            </th>
            <th class="category">
                {{mb_label object=$compte_rendu field=nom}}
                {{if $read_only}}
                    {{mb_field object=$compte_rendu field=nom readonly="readonly"}}
                {{else}}
                    {{mb_field object=$compte_rendu field=nom}}
                {{/if}}

                &mdash;
                {{mb_label object=$compte_rendu field=file_category_id}}
                <select name="file_category_id" style="width: 8em;" {{if $read_only}}disabled{{/if}}
                        onchange="FilesCategory.checkTypeDocDmpSisra(this.form, this.value);">
                    <option value="" {{if !$compte_rendu->file_category_id}}selected{{/if}}>
                        &mdash; {{tr}}None|f{{/tr}}</option>
                    {{foreach from=$listCategory item=currCat}}
                        <option value="{{$currCat->file_category_id}}"
                                {{if $currCat->file_category_id==$compte_rendu->file_category_id}}selected{{/if}}>{{$currCat->nom}}</option>
                    {{/foreach}}
                </select>

                {{if "dmp"|module_active}}
                    &mdash;
                    {{mb_label object=$compte_rendu field=type_doc_dmp}}
                    {{mb_field object=$compte_rendu field="type_doc_dmp" readonly=$read_only emptyLabel="Choose" style="width: 15em;"}}
                {{/if}}
                &mdash;
                {{mb_label object=$compte_rendu field=language}}
                {{mb_field object=$compte_rendu field=language readonly=$read_only}}

                {{if !$read_only}}
                    &mdash;
                    <button class="save notext singleclick">{{tr}}Save{{/tr}}</button>
                    <button type="button" class="trash notext singleclick"
                            onclick="deleteCr('{{$compte_rendu->_id}}');">{{tr}}Delete{{/tr}}</button>
                {{/if}}
              {{if $app->_ref_user->isPraticien() && !$compte_rendu->valide}}
                <button class="modify notext" title="{{tr}}dPBoard-msg-ask_correction{{/tr}}" onclick="Board.askCorrection('{{$compte_rendu->_id}}')"></button>
              {{/if}}
              {{if $app->_ref_user->isSecretaire() && !$compte_rendu->valide}}
                <button type="button" class="tick notext" title="{{tr}}dPBoard-msg-ask-validation{{/tr}}" onclick="getForm('change_compte_rendu_statut').onsubmit()">
                </button>
              {{/if}}

                <br/>

                {{if "sisra"|module_active}}
                    {{mb_label object=$compte_rendu field=type_doc_sisra}}
                    {{mb_field object=$compte_rendu field="type_doc_sisra" readonly=$read_only emptyLabel="Choose" style="width: 15em;"}}
                {{/if}}

                &mdash;

                {{mb_include module=files template=inc_button_masquage docitem=$compte_rendu}}

                {{if $compte_rendu->_id}}
                  {{if $compte_rendu->_ref_last_statut_compte_rendu->_id}}
                    {{mb_label object=$compte_rendu->_ref_last_statut_compte_rendu field=statut}}
                    {{mb_field object=$compte_rendu->_ref_last_statut_compte_rendu readonly=$read_only field="statut" id="statut_compte_rendu" style="width: 15em" onchange="changeCompteRenduStatut(this,'`$compte_rendu->_id`')"}}
                  &mdash;
                  {{/if}}
                {{/if}}
                {{mb_label object=$compte_rendu field=remis_patient typeEnum=checkbox}}
                {{mb_field object=$compte_rendu field=remis_patient typeEnum=checkbox readonly=$read_only onchange="this.form.onsubmit();"}}

                {{mb_label object=$compte_rendu field=private typeEnum=checkbox}}
                {{mb_field object=$compte_rendu field=private typeEnum=checkbox readonly=$read_only onchange="this.form.onsubmit();"}}

                {{mb_label object=$compte_rendu field=send typeEnum=checkbox}}
                {{mb_field object=$compte_rendu field=send typeEnum=checkbox readonly=$read_only onchange="this.form.onsubmit();"}}

                &mdash;
                {{mb_label object=$compte_rendu field=_is_locked typeEnum=checkbox onmouseover="ObjectTooltip.createEx(this, '`$compte_rendu->_guid`', 'locker')"}}
                {{mb_field object=$compte_rendu field=_is_locked typeEnum="checkbox" readonly=$lock_bloked onChange="checkLock(this);"}}

                {{if $compte_rendu->_id && $can_duplicate}}
                    &mdash;
                    {{me_button icon=duplicate label="CCompteRendu-action-Duplicate without archive" onclick="duplicateDoc(this.form);"}}
                    {{me_button icon=duplicate label="CCompteRendu-action-Duplicate with archive" onclick="\$V(this.form.annule, 1); duplicateDoc(this.form);"}}

                    {{me_dropdown_button button_icon=duplicate button_label="CCompteRendu-action-Duplicate" button_class="me-tertiary"
                    container_class="me-dropdown-button-right"}}
                {{/if}}

                {{if $pdf_and_thumbs}}
                    &mdash;
                    <button type="button" class="pagelayout me-tertiary" title="{{tr}}CCompteRendu-Pagelayout{{/tr}}"
                            {{if $read_only}}readonly disabled{{/if}}
                            onclick="save_page_layout();
                    Modal.open($('page_layout'), {
                      closeOnClick: $('page_layout').down('button.tick'),
                      width: '350px',
                      height: '450px'
                    });">
                        {{tr}}CCompteRendu-Pagelayout{{/tr}}
                    </button>
                    <div id="page_layout" style="display: none;">
                        {{mb_include module=compteRendu template=inc_page_layout droit=1}}
                        <button class="tick" type="button">{{tr}}Validate{{/tr}}</button>
                        <button class="cancel" type="button"
                                onclick="cancel_page_layout();">{{tr}}Cancel{{/tr}}</button>
                    </div>
                {{/if}}

                {{if $header_footer_fly}}
                    &mdash;
                    <button type="button" class="header_footer me-tertiary" onclick="modalHeaderFooter(1)"
                            title="Entête / pied de page à la volée"
                            {{if $read_only}}readonly disabled{{/if}}>
                        {{tr}}CCompteRendu-modify_header_footer{{/tr}}
                    </button>
                {{/if}}

                {{if $compte_rendu->_id && $compte_rendu->_ref_modele->object_id}}
                    {{assign var=modele value=$compte_rendu->_ref_modele}}
                    Version précédente :
                    <a onmouseover="ObjectTooltip.createEx(this, '{{$modele->_guid}}')" href="#1"
                       onclick="Document.edit('{{$modele->_id}}')">
                        {{$compte_rendu}}
                    </a>
                {{/if}}
            </th>
            {{if $user_opener->_id}}
                <th class="category narrow warning me-opener-base">
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user_opener initials=border}}

                    <div id="opener-tooltip" class="me-opener-tooltip">
                        {{tr}}CCompteRendu-user-opener-notification{{/tr}} {{$user_opener->_view}}
                        <button type="button" class="me-tertiary me-margin-top-8"
                                onclick="$('opener-tooltip').removeClassName('displayed');">
                            OK
                        </button>
                    </div>
                </th>
            {{/if}}
            <th class="category narrow" style="vertical-align: top; width: 85px;">
                {{if $compte_rendu->_id}}
                    <a style="float: right;" class="button right {{if !$prevnext.next}}disabled{{/if}}"
                            {{if $prevnext.next}}
                        href="?m=compteRendu&dialog=edit&compte_rendu_id={{$prevnext.next}}"
                            {{/if}}>
                        Suiv.
                    </a>
                    {{mb_include module=system template=inc_object_idsante400 object=$compte_rendu}}
                    {{mb_include module=system template=inc_object_history object=$compte_rendu}}
                {{/if}}
            </th>
        </tr>
    </table>

    <table class="form me-no-align">
        {{if !$compte_rendu->_id || !$read_only}}
            <tr>
                <td colspan="{{if $pdf_and_thumbs}}3{{else}}2{{/if}}">
                    <div id="reloadzones">
                        {{mb_include module=compteRendu template=inc_zones_fields}}
                    </div>
                </td>
            </tr>
        {{/if}}

        {{if $compte_rendu->_id && "dPfiles CDocumentSender system_sender"|gconf}}
            <tr>
                <th>
                    <script>
                        refreshSendButton = function () {
                            var url = new Url("files", "ajax_send_button");
                            url.addParam("item_guid", "{{$compte_rendu->_guid}}");
                            url.addParam("onComplete", "refreshSendButton()");
                            url.requestUpdate("sendbutton");
                            refreshList();
                        }
                    </script>
                    <label title="{{tr}}config-dPfiles-CDocumentSender-system_sender{{/tr}}">
                        {{tr}}config-dPfiles-CDocumentSender-system_sender{{/tr}}
                        <em>({{tr}}{{"dPfiles CDocumentSender system_sender"|gconf}}{{/tr}})</em>
                    </label>
                </th>
                <td id="sendbutton">
                    {{mb_include module=files template=inc_file_send_button
                    _doc_item=$compte_rendu
                    onComplete="refreshSendButton()"
                    notext=""}}
                </td>
            </tr>
        {{/if}}
        <tr>
            <td class="greedyPane" {{if !$pdf_and_thumbs}}colspan="2"{{/if}} style="width: 1200px;">
        <textarea id="htmlarea" name="_source" class="me-padding-top-0 me-padding-bottom-0">
          {{$templateManager->document}}
        </textarea>
            </td>
            {{if $pdf_and_thumbs}}
                <td id="thumbs_button" class="narrow me-valign-top">
                    <div id="mess" class="oldThumbs opacity-60" style="display: none;"></div>
                    <div id="thumbs"
                         style="overflow: auto; overflow-x: hidden; width: 300px; text-align: center; white-space: normal;"></div>
                </td>
            {{/if}}
        </tr>
    </table>
</form>
<form name="change_compte_rendu_statut" method="post" onsubmit="return onSubmitFormAjax(this,function(){
                            location.reload();
                          })">
  <input type="hidden" name="@class" value="CStatutCompteRendu"/>
  <input type="hidden" name="statut_compte_rendu_id"/>
  <input type="hidden" name="compte_rendu_id" value="{{$compte_rendu->_id}}"/>
  <input type="hidden" name="statut" value="attente_validation_praticien"/>
  <input type="hidden" name="datetime" value="now"/>
  <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
</form>
