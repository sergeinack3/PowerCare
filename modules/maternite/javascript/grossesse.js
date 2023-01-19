/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Grossesse = window.Grossesse || {
    formTo: null,
    formFrom: null,
    duree_sejour: null,
    submit: false,
    parturiente_id: null,
    large_icon: 0,
    modify_grossesse: 1,
    show_checkbox: 1,
    mod_tamm: 0,
    show_empty: 1,
    mode_tdb: 0,
    light_view: 0,
    is_edit_consultation: 0,

    viewGrossesses: function (parturiente_id, object_guid, form, show_checkbox) {
        Grossesse.show_checkbox = Object.isUndefined(show_checkbox) ? 1 : show_checkbox;
        var url = new Url("maternite", "bindPregnancy");
        if (parturiente_id == '') {
            url.addParam("parturiente_id", $V(form.patient_id));
        } else {
            url.addParam("parturiente_id", parturiente_id);
        }
        url.addNotNullParam("object_guid", object_guid);
        url.addParam('grossesse_id', (form && $V(form.grossesse_id)) ? $V(form.grossesse_id) : null);
        url.requestModal(1240, 650, {
            onClose: function () {
                if (!Grossesse.modify_grossesse) {
                    Grossesse.updateGrossesseArea();
                }
                Grossesse.updateEtatActuel();
                let object_class = object_guid.split('-')[0];
                let object_id = object_guid.split('-')[1];

                if ((object_class === 'CConsultation') && !Grossesse.is_edit_consultation) {
                    if (Control.Modal.stack) {
                        // On ferme la modale de la vue Grossesse
                        Control.Modal.close();
                        // Utilisation et vérification de window.parent car Consultation utilise .modal()
                        if (!window.parent || window.parent === window || !window.parent.Control) {
                            // Cas de l'onglet, on le refresh pour afficher le volet de suivi de grossesse
                            document.location.reload();
                            return;
                        }
                        // On ferme la modale de la consultation
                        window.parent.Control.Modal.close();
                        // On ré-ouvre la modale consultation avec le nouveau contenu
                        window.parent.Consultation.editModal(object_id);
                    }
                }
            }
        });
    },
    toggleGrossesse: function (sexe, form) {
        form.select(".button_grossesse")[0].disabled = sexe == "f" ? "" : "disabled";
    },
    editGrossesse: function (grossesse_id, parturiente_id) {
        var url = new Url("maternite", "ajax_edit_grossesse");
        url.addParam("grossesse_id", grossesse_id);
        url.addNotNullParam("parturiente_id", parturiente_id);
        url.requestUpdate("edit_grossesse");
    },
    viewPlanningGrossesse: function (grossesse_id) {
        var url = new Url('maternite', 'ajax_vw_calculatrice_obstetricale');
        url.addParam('grossesse_id', grossesse_id);
        url.requestModal(800);
    },
    refreshList: function (parturiente_id, object_guid, grossesse_id) {
        var url = new Url("maternite", "ajax_list_grossesses");
        url.addNotNullParam("parturiente_id", parturiente_id);
        url.addNotNullParam("object_guid", object_guid);
        url.addParam('grossesse_id', grossesse_id);
        url.addParam("show_checkbox", Grossesse.show_checkbox);
        url.requestUpdate("list_grossesses");
    },
    afterEditGrossesse: function (grossesse_id, grossesse) {
        var terme_area = $('terme_area');

        if (terme_area && $V(Grossesse.formTo.grossesse_id)) {
            var date = new Date.fromDATE(grossesse.terme_prevu);
            terme_area.update(date.toLocaleDate());
        }

        if (Grossesse.mode_tdb) {
            Control.Modal.close();
            Tdb.editGrossesse(grossesse_id);
        } else {
            Grossesse.editGrossesse(grossesse_id);
            Grossesse.refreshList();
        }
    },
    bindGrossesse: function (grossesse_id) {
        if (this.formFrom) {
            grossesse_id = $V(this.formFrom.unique_grossesse_id);
        }

        $V(this.formTo.grossesse_id, grossesse_id);

        if (this.mod_tamm == '0') {
            if (grossesse_id) {
                var input = this.formFrom ? this.formFrom.down("input[name='unique_grossesse_id']:checked") : null;
                var html = "<img src='style/mediboard_ext/images/icons/grossesse.png' ";
                html += "onmouseover=\"ObjectTooltip.createEx(this, 'CGrossesse-" + grossesse_id + "')\" ";
                if (input && input.get("active") == 0) {
                    html += "class='opacity-40' ";
                }
                if ($V(this.formTo._large_icon) == 1) {
                    html += "style='width: 30px; background-color: rgb(255, 215, 247);'";
                }
                html += "/>";
                $("view_grossesse").update(html);
                this.formTo.select(".button_grossesse")[0].show();
                if (this.formTo.sejour_id) {
                    $V(this.formTo.type_pec, 'O');
                    $V(this.formTo._duree_prevue, this.duree_sejour);
                }

                // Pour une nouvelle DHE, on applique la date de terme prévu sur l'entrée prévue
                if (this.formTo._date_entree_prevue && !$V(this.formTo.sejour_id)) {
                    var date = $(this.formFrom.name + '_unique_grossesse_id_' + $V(this.formFrom.unique_grossesse_id)).get("date");
                    $V(this.formTo._date_entree_prevue, date);
                    $V(this.formTo._date_entree_prevue_da, new Date(date).format("dd/MM/yyyy"));
                }
            } else {
                $("view_grossesse").update("<div class='empty' style='display: inline'>" + (Grossesse.show_empty ? $T("CGrossesse.none_linked") : '') + "</div>");
            }
        } else {
            if (grossesse_id) {
                if ($('button_create_pregnancy_tamm')) {
                    $('button_create_pregnancy_tamm').hide();
                }

                if ($('container_mater')) {
                    var content_html = "<span class='texticon texticon-grossesse' style='float:right'";
                    content_html += "onmouseover=\"ObjectTooltip.createEx(this, 'CGrossesse-" + grossesse_id + "')\"> ";
                    content_html += "<img src='./style/mediboard_ext/images/icons/grossesse.png' alt='{{tr}}CGrossesse{{/tr}}'>";
                    content_html += "<span>" + $T('CGrossesse.linked') + "</span></span>";

                    $('container_mater').update(content_html);
                }
            } else {
                $('container_mater').update('');
            }
        }

        var terme_area = $('terme_area');

        if (terme_area && this.formFrom) {
            var date_terme_prevu = '&mdash;';

            input = this.formFrom.down("input[name='unique_grossesse_id']:checked");

            if (input) {
                date_terme_prevu = Date.fromDATE(input.dataset.date);
                date_terme_prevu = date_terme_prevu.toLocaleDate();
            }

            terme_area.update(date_terme_prevu);
        }

        if (this.submit == "1") {
            return onSubmitFormAjax(this.formTo);
        }
    },
    emptyGrossesses: function () {
        if (this.formFrom) {
            this.formFrom.select("input[name='unique_grossesse_id']").each(function (input) {
                input.checked = "";
            });
        }
        this.bindGrossesse();
    },

    updateGrossesseArea: function () {
        if (!Grossesse.parturiente_id) {
            return;
        }

        var url = new Url("maternite", "ajax_update_grossesse_area");
        url.addParam("parturiente_id", Grossesse.parturiente_id);
        url.addParam("submit", Grossesse.submit);
        url.addParam("large_icon", Grossesse.large_icon);
        url.addParam("modify_grossesse", Grossesse.modify_grossesse);
        url.addParam("show_empty", Grossesse.show_empty);
        url.requestUpdate("view_grossesse");
    },
    /**
     * Update the current state
     *
     * @param light_view
     */
    updateEtatActuel: function (light_view) {
        if (!Grossesse.parturiente_id || !$("etat_actuel_grossesse")) {
            return;
        }

        var url = new Url("maternite", "ajax_update_fieldset_etat_actuel");
        url.addParam("patient_id", Grossesse.parturiente_id);
        url.addParam("light_view", light_view);
        url.requestUpdate($("etat_actuel_grossesse").up());
    },

    editOperation: function (operation_id, sejour_id, grossesse_id) {
        new Url("planningOp", "vw_edit_urgence")
            .addParam("operation_id", operation_id)
            .addParam("sejour_id", sejour_id)
            .addParam("grossesse_id", grossesse_id)
            .addParam("hour_urgence", new Date().getHours())
            .addParam("min_urgence", 0)
            .addParam("dialog", 1)
            .modal({
                width: "95%",
                height: "95%",
                onClose: function () {
                    document.location.reload();
                } // Laisser dans une fonction anonyme à cause de l'argument "period"
            });
    },

    askCancelIntervention: function (sejour_id) {
        new Url("maternite", "ajax_cancel_intervention")
            .addParam("sejour_id", sejour_id)
            .requestModal();
    },

    tdbGrossesse: function (grossesse_id, patient_id) {
        new Url("maternite", "ajax_edit_grossesse")
            .addParam("grossesse_id", grossesse_id)
            .addParam("parturiente_id", patient_id)
            .addParam("with_buttons", 1)
            .addParam("creation_mode", 0)
            .requestModal(800, 500, {
                onClose: function () {
                    if (!Grossesse.modify_grossesse) {
                        Grossesse.updateGrossesseArea();
                    }
                    Grossesse.updateEtatActuel();
                }
            });
    },
    /**
     * Show or Hide a field
     *
     * @param field_id
     * @param value_element
     */
    showField: function (field_id, value_element) {
        if (value_element == '1') {
            $(field_id).show();
        } else {
            $(field_id).hide();
        }
    }
};

