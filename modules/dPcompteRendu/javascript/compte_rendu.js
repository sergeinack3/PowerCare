/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

function openCorrespondants(compte_rendu_id, object_guid, show_modal, force_refresh) {
    if (!$('correspondants_area') || force_refresh) {
        var url = new Url("compteRendu", "ajax_edit_correspondants_courrier");
        url.addParam("compte_rendu_id", compte_rendu_id);
        url.addParam("object_guid", object_guid);
        url.requestUpdate("correspondants_courrier", function () {
            Control.Modal.position();
        });
    }
    if (show_modal) {
        var correspondants_courrier = $("correspondants_courrier");
        if (correspondants_courrier) {
            Modal.open(correspondants_courrier, {width: "800", height: "580"});
        }
    }
}

function playField(element, class_name, editor_element, name) {
    var modal = $('play_modal');
    var modal_add = $('add_field_area');
    var field_area = modal.down('td.field_aera');
    field_area.update();

    if (class_name == "name") {
        field_area.insert(new DOM.p({}, name));
    }

    var button_add = modal.down('button.add');

    field_area.insert(element);
    modal.down('button.tick').onclick = function () {
        replaceField(element, class_name);
    };

    // Ajout du double clic sur les options
    if (class_name == 'name') {
        button_add.show();
        $V(modal_add.down('input'), '');

        element.ondblclick = function () {
            replaceField(element, class_name);
        };

        modal_add.down('button.tick').onclick = function () {
            Control.Modal.close();
            var field_value = $V(modal_add.down('input'));
            element.insert(DOM.option({value: field_value}, field_value));
            element.selectedIndex = -1;
            $V(element, field_value);
            replaceField(element, class_name);
        };
    } else {
        button_add.hide();
    }

    modal.down('button.trash').onclick = function () {
        replaceField(element, class_name, 1);
    };
    modal.down('button.cancel').onclick = function () {
        Control.Modal.close();
        Element.setStyle(editor_element, {backgroundColor: ""});
    };

    // Réouverture si la modale est fermée
    if (!window.modal_mode_play || !window.modal_mode_play.isOpen) {
        window.modal_mode_play = Modal.open(modal, {
            draggable: modal.down('th.title'),
            overlayOpacity: 0.3,
            width: "500",
            height: "350"
        });

        var left = document.viewport.getDimensions().width - window.modal_mode_play.container.getDimensions().width;
        modal_mode_play.container.setStyle({top: 0, left: left + "px"});
    }
}

function callbackSave(id, obj) {
    $V(getForm("editFrm").compte_rendu_id, id);

    refreshZones(id, obj);
    ReadWriteTimer.storeSave(id);
}

function refreshZones(id, obj) {
    var editor = CKEDITOR.instances.htmlarea;
    var form = getForm("editFrm");
    $V(form.date_print, obj.date_print);
    $V(form.author_id, obj.author_id);

    if (Preferences.multiple_docs == "1" && id) {
        window.name = "cr_" + id;
    }

    var afterSetData = function () {
        // Dans le cas de la génération d'un document par correspondant,
        // mise à jour du nom du document dans la popup
        $V(getForm("editFrm").nom, obj.nom);
        $V(getForm("download-pdf-form")._ids_corres, obj._ids_corres);
        $V(getForm("download-pdf-form").compte_rendu_id, id);

        var refresh = function () {
        };
        if (window.Preferences.pdf_and_thumbs == 1) {
            Thumb.compte_rendu_id = id;
            Thumb.modele_id = 0;
            refresh = function () {
                window.thumbs_timeout = setTimeout(function () {
                    Thumb.refreshThumbs(0, Thumb.print);
                }, 0)
            };
        }

        var url = new Url("compteRendu", "edit");
        url.addParam("compte_rendu_id", id);
        url.addParam("reloadzones", 1);
        url.requestUpdate("reloadzones", {
            onComplete:
                function () {
                    refresh();
                    window.resizeEditor();
                    var form = getForm("editFrm");
                    $V(form.compte_rendu_id, id);
                    if (Thumb.print) {
                        pdfAndPrintServer(id);
                    } else if (window.callback) {
                        window.callback();
                    }
                    form.onsubmit = function () {
                        Url.ping({onComplete: submitCompteRendu});
                        return false;
                    };
                    if (editor.getCommand('save')) {
                        editor.getCommand('save').setState(CKEDITOR.TRISTATE_OFF);
                    }
                    if (editor.getCommand('mbprintPDF')) {
                        editor.getCommand('mbprintPDF').setState(CKEDITOR.TRISTATE_OFF);
                    }
                    if (editor.getCommand('mbprint')) {
                        editor.getCommand('mbprint').setState(CKEDITOR.TRISTATE_OFF);
                    }
                }
        });
    };

    // Remise du content sauvegardé, avec le refresh des vignettes si dispo, et/ou l'impression en callback
    editor.setData(obj._source, afterSetData);
    window.saving_doc = false;
}

function saveAndMerge() {
    Control.Modal.close();
    var form = getForm('editFrm');
    $V(form.do_merge, 1);
    form.onsubmit();
}

function checkLock(oCheckbox) {

    if ($V(oCheckbox) == 1 || $V(oCheckbox) === "a_envoyer" ) {
        Modal.open('lock_area', {width: '400px', height: '330px'});

        var lockForm = getForm('LockDocOther');
        var editForm = getForm('editFrm');

        lockForm.user_password.focus();
        lockForm.down(".change-owner-container").setVisible($V(editForm.elements.private) == 1);
    } else {
        var form = oCheckbox.form;
        $V(form.valide, 0);
        $V(form.locker_id, "");
        $V(form.callback, "afterLock");
        form.onsubmit();
    }
}

function toggleLock(user_id, form_name) {

    var form = getForm('editFrm');
    var formLock = getForm(form_name);

    $V(form.valide, $V(form.valide) == '1' ? 0 : 1);
    $V(form.locker_id, user_id);
    $V(form.callback, "afterLock");
    if (formLock) {
        // Changement de propriétaire si demandé
        if ($V(form.valide) == '1' && formLock.change_owner.checked) {
            $V(form.author_id, user_id);
        }
    }

    form.onsubmit();
    Control.Modal.close();
}

function afterLock() {
    location.reload();
}

function modalHeaderFooter(state) {
    var form = getForm("editFrm");
    if (state) {
        window.save_header_id = $V(form.header_id);
        window.save_footer_id = $V(form.footer_id);
        Modal.open("header_footer_fly");
    } else {
        Control.Modal.close();
        $V(form.header_id, window.save_header_id);
        $V(form.footer_id, window.save_footer_id);
    }
}

function duplicateDoc(form) {
    var element = CKEDITOR.instances.htmlarea.element;
    $V(form.modele_id, $V(form.compte_rendu_id));
    $V(form.compte_rendu_id, '');
    $V(form.private, 0);
    $V(form.valide, 0);
    $V(form.locker_id, "");
    element.$.disabled = false;
    element.$.contentEditable = true;
    element.$.designMode = "On";
    $V(form.callback, "afterDuplicate");
    form.onsubmit();
}

function afterDuplicate(cr_id) {
    window.opener.Document.edit(cr_id);
    if (Preferences.multiple_docs == "1") {
        window.close();
    }
}

function submitCompteRendu(callback, oncomplete) {
    var editor = CKEDITOR.instances.htmlarea;

    if (!editor.document || window.saving_doc) {
        return;
    }

    window.saving_doc = true;

    editor.document.getBody().setStyle("background", "#ddd");

    var mess = null;
    if (mess = $('mess')) {
        mess.stopObserving("click");
    }
    (function () {
        if (Prototype.Browser.IE) {
            restoreStyle();
        }
        var html = editor.getData();
        if (Prototype.Browser.IE) {
            window.save_style = deleteStyle();
        }
        $V($("htmlarea"), html, false);

        var form = getForm("editFrm");

        if (checkForm(form) && User.id) {
            if (editor.getCommand('save')) {
                editor.getCommand('save').setState(CKEDITOR.TRISTATE_DISABLED);
            }
            if (editor.getCommand('mbprint')) {
                editor.getCommand('mbprint').setState(CKEDITOR.TRISTATE_DISABLED);
            }
            if (editor.getCommand('mbprintPDF')) {
                editor.getCommand('mbprintPDF').setState(CKEDITOR.TRISTATE_DISABLED);
            }
            editor.on("key", loadOld);

            form.onsubmit = function () {
                return true;
            };
            if (Thumb.modele_id && Thumb.contentChanged) {
                emptyPDF();
            }
            clearTimeout(window.thumbs_timeout);

            var dests = $("destinataires");

            if (dests && dests.select("input:checked").length) {
                $V(form.do_merge, 1);
            }

            onSubmitFormAjax(form, {
                useDollarV: true, onComplete: function () {
                    Thumb.contentChanged = false;
                    refreshListDocs();

                    if (oncomplete) {
                        oncomplete();
                    }

                    window.callback = callback ? callback : null;
                }
            }, $("systemMsg"));
        }
    }).defer();
}

function printServer(printer_guid) {
    new Url("compteRendu", "ajax_print")
        .addParam("printer_guid", printer_guid)
        .addParam("file_id", Thumb.file_id)
        .requestUpdate("systemMsg");
}

function deleteCr(compte_rendu_id) {
    if (!confirm($T('CCompteRendu-Confirm deletion'))) {
        return;
    }

    new Url()
        .addParam('@class', 'CCompteRendu')
        .addParam('compte_rendu_id', compte_rendu_id)
        .addParam('del', 1)
        .requestUpdate(
            'systemMsg',
            {
                method: 'POST',
                onComplete: () => {
                    refreshListDocs();
                    window.close();
                }
            }
        )
}

/**
 * Change the report status
 * @param statut_compte_rendu
 * @param compte_rendu_id
 */
function changeCompteRenduStatut(statut_compte_rendu, compte_rendu_id) {

  let form = getForm('change_compte_rendu_statut');
  if (statut_compte_rendu.value === 'attente_correction_secretariat') {
    Board.askCorrection(compte_rendu_id);
  } else if (statut_compte_rendu.value === 'a_envoyer') {
    this.checkLock(statut_compte_rendu);
  } else {
    $V(form.elements['statut'], statut_compte_rendu.value)
    form.onsubmit();
  }
}

/**
 * Disable some element if the user is a secretary
 * @param element
 * @param is_secretaire
 */
function setStatutDisabled(element, is_secretaire) {
  if (!element) {
    return;
  }
  for (let i = 0; i < element.options.length; i++) {

    if (is_secretaire && element.options[i].value !== 'attente_validation_praticien') {
      element.options[i].setAttribute('disabled', 'disabled');
    }
    if (!is_secretaire && (element.options[i].value === 'attente_validation_praticien' || element.options[i].value === 'envoye')) {
      element.options[i].setAttribute('disabled', 'disabled');
    }
  }
}
