/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DossierMater = {
    FIRST_ULTRASOUND_WEEK : 84,
    SECOND_ULTRASOUND_WEEK: 154,
    THIRD_ULTRASOUND_WEEK : 224,
    SICK_LEAVE_WEEK       : -42,

    currentPage: new Url,
    listForms: [],
    listGraphMos: "",
    _patient_id: 0,

    openPage: function (grossesse_id, page, operation_id) {
        var url = new Url("maternite", "dossier_mater_" + page);
        url.addNotNullParam("grossesse_id", grossesse_id);
        url.addParam("operation_id", operation_id);

        if (page == 'partogramme' || page == 'graphique_sspi') {
            url.addParam("isDossierPerinatal", 1);
        }

        this.currentPage = url.requestModal("95%", "95%", {
            showClose: true,
            onClose: DossierMater.refreshArea.curry(grossesse_id, "dossier_perinatal", operation_id, page)
        });
    },

    refreshArea: function (grossesse_id, page, operation_id, child_page) {
        new Url("maternite", "dossier_mater_" + page)
            .addNotNullParam("grossesse_id", grossesse_id)
            .addParam("operation_id", operation_id)
            .requestUpdate("dossier_mater_" + page, {
                onComplete: function () {
                    if (child_page == 'antecedents') {
                        DossierMater.reloadAtcd(DossierMater._patient_id);
                    }
                }
            });
    },

    refreshEntreeSortie: function (sejour_id, page) {
        new Url("maternite", "dossier_mater_" + page)
            .addNotNullParam("sejour_id", sejour_id)
            .requestUpdate("dossier_mater_" + page);
    },

    refresh: function () {
        DossierMater.currentPage.refreshModal();
    },

    prepareAllForms: function () {
        DossierMater.listForms.each(
            function (form) {
                form.observe("ui:change", DossierMater.changeForm.curry(form));
                form.observe("change", DossierMater.changeForm.curry(form));
            }
        )
    },

    changeForm: function (form) {
        var element = form._count_changes;
        var newValue = parseInt($V(form._count_changes));
        newValue++;
        $V(element, newValue, false);
    },

    submitAllForms: function (callBack) {
        var form = DossierMater.listForms.shift();
        if (form && $V(form._count_changes) > 0) {
            onSubmitFormAjax(form, function () {
                if (!DossierMater.listForms.length) {
                    if (callBack instanceof Function) {
                        callBack();
                    }
                } else {
                    DossierMater.submitAllForms(callBack);
                }
            });
        } else {
            if (!DossierMater.listForms.length) {
                if (callBack instanceof Function) {
                    callBack();
                }
            } else {
                DossierMater.submitAllForms(callBack);
            }
        }
    },

    addDepistage: function (depistage_id, grossesse_id) {
        var url = new Url("maternite", "edit_depistage");
        url.addNotNullParam("depistage_id", depistage_id);
        url.addNotNullParam("grossesse_id", grossesse_id);
        url.requestModal("95%", "95%", {showClose: true});
    },

    addGrossesseAnt: function (grossesse_ant_id, grossesse_id) {
        var url = new Url("maternite", "edit_grossesse_ant");
        url.addNotNullParam("grossesse_ant_id", grossesse_ant_id);
        url.addNotNullParam("grossesse_id", grossesse_id);
        url.requestModal("95%", "95%", {showClose: true});
    },
    /**
     * Add ultrasound monitoring
     *
     * @param echographie_id
     * @param grossesse_id
     * @param date
     */
    addEchographie: function (echographie_id, grossesse_id, date) {
        new Url("maternite", "edit_echographie")
            .addParam("echographie_id", echographie_id)
            .addNotNullParam("grossesse_id", grossesse_id)
            .addParam("date", date)
            .requestModal("95%", "95%", {showClose: true});
    },

    addExamenNouveauNe: function (examen_nouveau_ne_id, grossesse_id) {
        var url = new Url("maternite", "edit_examen_nouveau_ne");
        url.addNotNullParam("examen_nouveau_ne_id", examen_nouveau_ne_id);
        url.addNotNullParam("grossesse_id", grossesse_id);
        url.requestModal("95%", "95%", {showClose: true});
    },
    /**
     * Prepare graph selected
     *
     * @param grossesse_id
     * @param graph_name
     * @param num_enfant
     * @param show_select_children
     * @returns {*|Url}
     */
    prepareGraph: function (grossesse_id, graph_name, num_enfant, show_select_children) {
        return new Url('maternite', 'vw_echographie_graph')
            .addParam('grossesse_id', grossesse_id)
            .addParam('num_enfant', num_enfant)
            .addParam('show_select_children', show_select_children)
            .addParam('graph_name', graph_name);
    },
    /**
     * Show graph in modal
     *
     * @param grossesse_id
     * @param graph_name
     * @param num_enfant
     */
    showModalGraph: function (grossesse_id, graph_name, num_enfant) {
        DossierMater.prepareGraph(grossesse_id, graph_name, num_enfant, 1)
            .addParam('graph_size', '850px')
            .requestModal();
    },
    /**
     * Display graph
     *
     * @param grossesse_id
     * @param graph_name
     * @param elem
     * @param num_enfant
     * @param options
     * @param show_select_children
     */
    displayGraph: function (grossesse_id, graph_name, elem, num_enfant, options, show_select_children) {
        DossierMater.prepareGraph(grossesse_id, graph_name, num_enfant, show_select_children)
            .addParam('graph_size', '100%')
            .requestUpdate(elem, options);
    },

    editTP: function (patient_id) {
        new Url("maternite", "ajax_edit_tp")
            .addParam("patient_id", patient_id)
            .requestModal(1200, null, {onClose: DossierMater.refreshTP.curry(patient_id)});
    },

    editAtcd: function (patient_id, type) {
        new Url("maternite", "ajax_edit_atcd")
            .addParam("patient_id", patient_id)
            .addParam("type", type)
            .requestModal("60%", "60%", {
                onClose: function () {
                    DossierMater.refreshAtcd(patient_id, type);
                }
            });
    },

    refreshTP: function (patient_id, edit, elt_id) {
        new Url("maternite", "ajax_list_tp")
            .addParam("patient_id", patient_id)
            .addParam("edit", edit)
            .requestUpdate(elt_id ? elt_id : "traitement_personnel");
    },

    refreshAtcd: function (patient_id, type, edit, elt_id) {
        new Url("maternite", "ajax_list_atcd")
            .addParam("patient_id", patient_id)
            .addParam("type", type)
            .addParam("edit", edit)
            .requestUpdate(elt_id ? elt_id : "atcd_" + type, {
                onClose: function () {
                    DossierMater.reloadAtcd(patient_id);
                }
            });
    },

    reloadAtcd: function (patient_id) {
        new Url('patients', 'httpreq_vw_antecedent_allergie')
            .addParam('patient_id', patient_id)
            .requestUpdate('atcd_allergies', {
                insertion: function (element, content) {
                    element.innerHTML = content;
                }
            });
    },

    print: function (grossesse_id) {
        new Url("maternite", "dossier_mater_print")
            .addParam("grossesse_id", grossesse_id)
            .requestModal("70%", "70%");
    },
    addConsultationPostNatale: function (dossier_perinat_id) {
        var url = new Url("maternite", "ajax_edit_consultation_post_natale");
        url.addParam('dossier_perinat_id', dossier_perinat_id);
        url.requestModal("70%", "70%");
    },
    onSubmitConsultPostNatale: function (form, modale) {
        if ($V(form.consultation_post_natale_id) || ($V(form.date) && $V(form.consultant_id)) || modale) {
            onSubmitFormAjax(form, function () {
                if (modale) {
                    Control.Modal.close();
                }
                if (!$V(form.consultation_post_natale_id)) {
                    Control.Modal.refresh();
                }
            });
        }
    },

    // Lance la vue mosaïque si un ou plusieurs graphiques ont été sélectionnés
    graphMosMode: function (grossesse_id) {
        if (DossierMater.listGraphMos !== "") {
            new Url("maternite", "ajax_vw_grossesse_graph_mos")
                .addParam("list_graph", DossierMater.listGraphMos.substr(1))
                .addParam('grossesse_id', grossesse_id)
                .pop('100%', '100%');
        } else {
            alert($T("CDossierPerinat.select_graph_before"));
        }
    },

    // Ajoute un graphique à la sélection des graph. pour la vue mosaïque
    //  + Gestion de la checkbox "Sélectionner / Désélectionner tout"
    graphMosCheckbox: function (checkbox, lib_cat, iterator) {
        if (checkbox.checked && DossierMater.listGraphMos.indexOf("|" + lib_cat) === -1) {
            DossierMater.listGraphMos += "|" + lib_cat;
        } else if (!checkbox.checked) {
            DossierMater.listGraphMos = DossierMater.listGraphMos.replace("|" + lib_cat, "");
        }
        $$('.mosaique-checkbox-' + iterator).each(function (Element) {
            Element.checked = checkbox.checked
        });
        allChecked = true;
        allUnchecked = true;
        checkbox.up('table').select('input.mosaique-checkbox').each(function (Element) {
            allChecked = (Element.checked && allChecked);
            allUnchecked = (!Element.checked && allUnchecked);
        });
        $$('input.mosaique-all-checkbox').each(function (Element) {
            Element.setStyle({opacity: ((!allChecked && !allUnchecked) ? 0.5 : 1)})
                .checked = !allUnchecked;
        });
    },

    // Sélectionne ou désélectionne tous les graphiques pour la vue mosaïque
    graphMosAllCheckbox: function (checkbox) {
        allChecking = checkbox.checked;
        checkbox.up('table').select('input.mosaique-checkbox').each(function (Element) {
            if (Element.checked !== allChecking) {
                Element.click();
            }
        });
    },

    // Récupère les graphiques depuis la vue mosaïque
    invokeGraphMosContent: function (grossesse_id, list_graph, num_enfant, other_name_element) {
        var graphLoaded = 0;

        list_graph.split('|').each(function (graph, index) {
            var name_element = 'graph_mos_container_' + (index + 1);

            name_element = other_name_element ? name_element + '' + other_name_element : name_element;

            DossierMater.displayGraph(grossesse_id, graph, name_element, num_enfant, function () {
                graphLoaded++;
                if (graphLoaded === list_graph.split('|').length) {
                    $$('.placeholder_mater').each(function (Element) {
                        Element.setStyle({height: '400px'})
                    });
                }
            }, '0');
        });
    },
    onSubmitAccouchement: function (form) {
        onSubmitFormAjax(form, function () {
            if (!$V(form.accouchement_id)) {
                Control.Modal.refresh();
            }
        });
    },

    // Ouvre la vue des antécédents et traitements
    openAtcdAndTP: function (patient_id) {
        var url = new Url("cabinet", "listAntecedents");
        url.addParam("patient_id", patient_id);
        url.addParam("sejour_id", "");
        url.addParam("show_header", 0);
        url.requestModal("80%", "80%", {
            onClose: function () {
                Control.Modal.refresh();
            }
        });
    },
    /**
     * Inform the number fetuse
     *
     * @param grossesse_id
     */
    informNumberFetuse: function (grossesse_id) {
        new Url("maternite", "ajax_number_fetuse")
            .addParam("grossesse_id", grossesse_id)
            .requestModal("30%", "20%", {
                onClose: function () {
                    Control.Modal.refresh();
                }
            });
    },
    /**
     * Copy the values on the other forms
     */
    copyValuesOtherForm: function (element) {
        var element_name = element.name;

        DossierMater.listForms.each(function (form) {
            $V(form.elements[element_name], element.value);
        });
    },

    /**
     * Add or remove antecedents form perinatal folder to medical folder
     */
    deleteAntecedent: function (form_perinatal, form, atcd_rques, atcd_textarea, type) {
        var url = new Url("patients", "ajax_find_antecedent");
        url.addParam("rques", atcd_textarea ? atcd_textarea : atcd_rques);
        url.addParam("patient_id", $V(form._patient_id));
        url.requestJSON(function (atcd) {
            if (atcd[0] > 0) {
                $V(form.antecedent_id, atcd[0]);
                $V(form.del, 1);

                onSubmitFormAjax(form_perinatal, function () {
                    onSubmitFormAjax(form, DossierMater.refreshAtcd.curry($V(form._patient_id), type))
                });
            }
        });
    },
    /**
     * Find the antecedent to delete
     */
    manageAntecedents: function (element, prefix, atcd_textarea) {
        var form_atcd = getForm('addAntecedentDossierMedical');
        var type = 'med';
        var appareil = '';
        var element_name = element.name;
        var atcd_rques = (element.tagName == 'TEXTAREA') ? element.value : $T('CDossierPerinat-' + element_name + '-court');
        var patient_id = $V(form_atcd._patient_id);

        if (prefix === 'chir_ant_') {
            type = 'chir';
        } else if (prefix === 'gyneco_ant_') {
            type = '';
            appareil = 'gyneco_obstetrique';
        } else if (prefix === 'ant_fam_') {
            type = 'fam';
        }

        if ($V(element) == 1 || (element.tagName == 'TEXTAREA' && element.value)) {
            $V(form_atcd.type, type);
            $V(form_atcd.appareil, appareil);
            $V(form_atcd.rques, atcd_rques);
            $V(form_atcd.del, 0);
            $V(form_atcd.antecedent_id, '');

            onSubmitFormAjax(element.form, function () {
                onSubmitFormAjax(form_atcd, DossierMater.refreshAtcd.curry(patient_id, type))
            });
        } else {
            // find the antecedent into medical folder to delete
            DossierMater.deleteAntecedent(element.form, form_atcd, atcd_rques, atcd_textarea, type);
        }
    },
    /**
     * Print the summary sheet
     *
     * @param grossesse_id
     */
    printSummary: function (grossesse_id) {
        new Url("maternite", "print_fiche_synthese")
            .addParam("grossesse_id", grossesse_id)
            .popup(1000, 1000);
    },
    /**
     * Update the pregnancy timeline
     *
     * @param pregnancy_id
     */
    timelinePregnancy: function (pregnancy_id) {
        new Url('maternite', 'ajax_timeline_pregnancy')
            .addParam('pregnancy_id', pregnancy_id)
            .requestUpdate('timeline_pregnancy');
    },
    /**
     * Refresh the perinatal folder
     *
     * @param {int} grossesse_id
     */
    refreshDossierPerinat: function (grossesse_id) {
        var target = $('edit_dossier_perinat');
        if (target) {
            var url = new Url('maternite', 'ajax_edit_grossesse');
            url.addParam('grossesse_id', grossesse_id);
            url.addParam('with_buttons', 1);
            url.requestUpdate('edit_dossier_perinat');
        }
    },
    /**
     * Reload the prenancy content
     *
     * @param {int} grossesse_id
     */
    reloadGrossesse: function (grossesse_id) {
        var target = $('edit_grossesse');
        if (target) {
            var url = new Url('maternite', 'dossier_mater_identification');
            url.addParam('grossesse_id', grossesse_id);
            url.requestUpdate('edit_grossesse');
        }
    },
    /**
     * Reload the historical content
     *
     * @param {int} grossesse_id
     */
    reloadHistorique: function (grossesse_id) {
        var target = $('list_historique');
        if (target) {
            var url = new Url('maternite', 'ajax_grossesse_history');
            url.addParam('grossesse_id', grossesse_id);
            url.requestUpdate('list_historique');
        }
    },
    /**
     * Immediate consultation
     *
     * @param {int} prat_id
     * @param {int} grossesse_id
     * @param {function} callback
     * @returns {boolean}
     */
    consultNow: function (prat_id, grossesse_id, callback) {
        var form = getForm('editConsultImm');
        $V(form._prat_id, prat_id);
        $V(form.grossesse_id, grossesse_id);
        form.onsubmit();
        $V("selector_prat_imm", '', false);

        if (callback instanceof Function) {
            callback();
        }
        return false;
    },
    /**
     * Open a consultation after create it
     *
     * @param {int} _id
     * @param {int} grossesse_id
     */
    afterCreationConsultNow: function (_id, grossesse_id) {
        Consultation.editModal(_id, null, '', DossierMater.reloadHistorique(grossesse_id));
    },

    /**
     * Update the weeks
     */
    updateSemaines: function () {
        let form        = getForm("editFormGrossesse"),
            terme_prevu = new Date($V(form.terme_prevu));


        // 41 semaines pour la date de début
        terme_prevu.addDays(-287);
        var now = new Date();
        $("_semaine_grossesse").update(Math.ceil((now.getTime() - terme_prevu.getTime()) / (3600000 * 24 * 7)));
    },
    /**
     * Update the active field
     */
    updateActive: function (value) {
        if (value != '') {
            $('editFormGrossesse_active').value = '0';
        } else {
            $('editFormGrossesse_active').value = '1';
        }
    },
    /**
     * Update the expected term
     */
    updateTermePrevu: function () {
        var form = getForm("editFormGrossesse");

        var date_ddr = $V(form.date_dernieres_regles);
        var date_debut = $V(form.date_debut_grossesse);
        var rang = $V(form.rang);
        var cycle = $V(form.cycle);
        var terme_prevu_ddr = $("terme_prevu_ddr");
        var terme_prevu_debut_grossesse = $("terme_prevu_debut_grossesse");
        var date = null;

        terme_prevu_ddr.update();
        terme_prevu_debut_grossesse.update();

        if (!rang) {
            terme_prevu_debut_grossesse.update();
            terme_prevu_ddr.update();
            return;
        }

        if (date_debut) {
            date = Date.fromDATE(date_debut);
            date.addDays(272);
            terme_prevu_debut_grossesse.update(date.toLocaleDate());

            if ($V(form.grossesse_id)
                && date.toDATE() !== $V(form.terme_prevu)
                && confirm($T('CGrossesse-Ask use new terme prevu', date.toLocaleDate()))
            ) {
                DossierMater.useTermePrevu('DG');
            }
        }

        if (!cycle) {
            terme_prevu_ddr.update();
            return;
        }

        if (date_ddr) {
            date = Date.fromDATE(date_ddr);
            cycle = parseInt(cycle) - 15;
            date.addDays(272);
            date.addDays(cycle);

            terme_prevu_ddr.update(date.toLocaleDate());
        }
    },
    /**
     * Use the expected term
     *
     * @param {string} type
     */
    useTermePrevu: function (type) {
        var form = getForm("editFormGrossesse");
        terme_prevu_calc = type == "DDR" ? $("terme_prevu_ddr").getText() : $("terme_prevu_debut_grossesse").getText();
        if (terme_prevu_calc) {
            $V(form.terme_prevu_da, terme_prevu_calc);
            $V(form.terme_prevu, Date.fromLocaleDate(terme_prevu_calc).toDATE(), false);
        }
        form.terme_prevu.fire("ui:change");
    },
    /**
     * Edit the stay
     *
     * @param {int} _id
     * @param {int} grossesse_id
     * @param {int} patiente_id
     */
    editSejour: function (_id, grossesse_id, patiente_id) {
        var url = new Url('dPplanningOp', 'vw_edit_sejour');
        url.addParam('sejour_id', _id);
        url.addParam('grossesse_id', grossesse_id);
        url.addParam('patient_id', patiente_id);
        url.addParam("dialog", 1);
        url.modal({
            width: "95%",
            height: "95%",
            afterClose: function () {
                DossierMater.reloadHistorique(grossesse_id);
            }
        });
    },
    /**
     * Show the button timeline
     *
     * @param {string} container
     */
    showButtonTimeline: function (container) {
        $('button_timeline_patient').toggle();
        $('button_timeline_prenancy').toggle();

        if (container == 'button_timeline_prenancy') {
            $('title_timeline').innerText = $T('CConsultation-Timeline patient');
        } else if (container == 'button_timeline_patient') {
            $('title_timeline').innerText = $T('CGrossesse-Timeline');
        }
    },
    /**
     * View to update the perinatal folder
     *
     * @param grossesse_id
     */
    updateFolder: function (grossesse_id) {
        var url = new Url('maternite', 'ajax_vw_edit_perinatal_folder');
        url.addParam('grossesse_id', grossesse_id);
        url.requestModal("100%", "100%", {showClose: 0});
    },
    /**
     * Selected the menu
     *
     * @param element
     */
    selectedMenu: function (element) {
        $$('div.title_menu_container').invoke('removeClassName', 'title_menu_selected');
        element.up('div.title_menu_container').addClassName('title_menu_selected')
    },
    /**
     * Change the color rhesus
     *
     * @param element
     */
    changeColorRhesus: function (element) {
        if (element.up('label').hasClassName('label_rhesus_pos') && (element.checked === true)) {
            element.up('label').addClassName('label_rhesus_pos_selected');
            $('rhesus_neg').up('label').removeClassName('label_rhesus_neg_selected');
        } else {
            element.up('label').addClassName('label_rhesus_neg_selected');
            $('rhesus_pos').up('label').removeClassName('label_rhesus_pos_selected');
        }
    },

    onScroll: function (container) {
        var containerBottom = container.scrollTop + container.getHeight();
        var containerMiddle = (container.scrollTop + containerBottom) / 2;
        $$('div.title_menu_container').each(function (elt) {
            elt.removeClassName("title_menu_selected");
        });

        var cards_object = {};
        var cards_position = [];

        $$('div.container_card').each(
            function (element) {
                var posElement = Element.cumulativeOffset(element);
                var innerTop = posElement.top + 10;

                cards_object[element.id] = innerTop - containerMiddle;
            }
        );

        for (var key in cards_object) {
            cards_position.push([key, cards_object[key]]);
        }

        // sort
        cards_position.sort(function (a, b) {
            a = a[1];
            b = b[1];

            return Math.abs(1 - a) - Math.abs(1 - b);
        });

        $('menu_' + cards_position[0][0]).addClassName("title_menu_selected");
    },
    /**
     * Show or hide elements
     *
     * @param element
     * @param container
     * @param class_containers
     * @param me_form_field
     */
    ShowElements: function (element, container, class_containers, me_form_field) {
        var element_name = element.name;
        var element_value = element.value;
        var form = element.form;
        var bool_element_names = [
            'multiple',
            'pathologie_grossesse',
            'rhesus',
            'subst_avant_grossesse',
            'subst_debut_grossesse',
            'biopsie_trophoblaste',
            'amniocentese',
            'cordocentese',
        ];

        var other_element_names = [
            'resultat_prelevement_vaginal',
            'resultat_prelevement_urinaire',
            'pelvimetrie',
            'activite_pro',
        ];

        var other_element_values = [
            'autre',
            'anorm',
            'a',
        ];

        var class_containers_element = $$(class_containers);
        var container_element = $(container);
        var containers;
        var class_immuno = 'immuno-hematology-none';

        if (container && container.includes('|')) {
            containers = container.split('|');
        }

        if (bool_element_names.includes(element_name) && container) {
            if (element_value == 1) {
                container_element.show();
            } else {
                container_element.hide();
            }
        } else if (bool_element_names.includes(element_name) && !container) {
            if (element_value == 'neg') {
                class_containers_element.invoke('show');
            } else {
                class_containers_element.invoke('hide');
            }
        } else if (element_name == 'situation_accompagnement') {
            if (element_value && element_value != 'n') {
                class_containers_element.invoke('show');
            } else {
                class_containers_element.invoke('hide');
            }
        } else if (element_name == 'genotypage') {

            if (element_value) {
                if (element_value != 'nonfait') {
                    $(containers[0]).up('td').removeClassName(class_immuno);
                    $(containers[1]).up('td').addClassName(class_immuno);
                } else {
                    $(containers[0]).up('td').addClassName(class_immuno);
                    $(containers[1]).up('td').removeClassName(class_immuno);
                }
            } else {
                $(containers[0]).up('td').addClassName(class_immuno);
                $(containers[1]).up('td').addClassName(class_immuno);
            }
        } else if (element_name == 'rhophylac') {
            var first_container = containers[0];
            var date_quantity_containers = first_container.includes('&') ? first_container.split('&') : null;

            if (element_value) {
                if (element_value != 'nonfait') {
                    if (date_quantity_containers) {
                        $(date_quantity_containers[0]).up('td').removeClassName(class_immuno);
                        $(date_quantity_containers[1]).up('td').removeClassName(class_immuno);
                    }

                    $(containers[1]).up('td').addClassName(class_immuno);
                } else {
                    if (date_quantity_containers) {
                        $(date_quantity_containers[0]).up('td').addClassName(class_immuno);
                        $(date_quantity_containers[1]).up('td').addClassName(class_immuno);
                    }

                    $(containers[1]).up('td').removeClassName(class_immuno);
                }
            } else {
                if (date_quantity_containers) {
                    $(date_quantity_containers[0]).up('td').addClassName(class_immuno);
                    $(date_quantity_containers[1]).up('td').addClassName(class_immuno);
                }

                $(containers[1]).up('td').addClassName(class_immuno);
            }
        } else if (other_element_names.includes(element_name)) {
            if (element_value && other_element_values.includes(element_value)) {
                container_element.show();
            } else {
                container_element.hide();
            }
        }
    },
    /**
     * Check user action
     *
     * @param close
     */
    checkAction: function (close) {
        if ((close == 1) && confirm($T('CDossierPerinat-msg-Are you sure you want to quit editing the folder'))) {
            Control.Modal.close();
        }
    },
    /**
     * Binding datas
     *
     * @param element
     * @param form
     */
    bindingDatas: function (element, form) {
        var element_name = element.name;
        var element_value = element.value;

        if (element_name == '_date_depistage') {
            element_name = 'date';
        }

        $V(form.elements[element_name], element_value);
    },
    /**
     * Refresh the mother's pathologies
     *
     * @param dossier_perinatal_id
     */
    refreshMotherPathologies: function (dossier_perinatal_id) {
        var url = new Url("maternite", "motherPathologiesTags");
        url.addParam("dossier_perinat_id", dossier_perinatal_id);
        url.requestUpdate('pathologies_tags');
    },
    /**
     * Get the mother's pathologies
     *
     * @param form
     * @param dossier_perinatal_id
     */
    getMotherPathologies: function (form, dossier_perinatal_id) {
        var form_patho = getForm('edit_mother_pathologies');

        var url = new Url("maternite", "motherPathologiesAutocomplete");
        url.addParam("input_field", "_pathology_name");
        url.addParam("dossier_perinat_id", dossier_perinatal_id);
        url.autoComplete(form.elements._pathology_name, 'pathology_list', {
            minChars: 3,
            method: "get",
            dropdown: true,
            width: "300px",
            afterUpdateElement: function (field, selected) {
                var pathologie_name = selected.get('pathologie_name');
                $V(form_patho.elements[pathologie_name], 1);
                $V(field, '');
            }
        });
    },
    /**
     * Refresh the other screenings
     *
     * @param dossier_perinatal_id
     */
    refreshOtherScreenings: function (dossier_perinatal_id) {
        var url = new Url("maternite", "motherPathologiesTags");
        url.addParam("dossier_perinat_id", dossier_perinatal_id);
        url.requestUpdate('pathologies_tags');
    },
    /**
     * Get the other screenings
     *
     * @param form
     * @param depistage_grossesse_id
     */
    getOtherScreenings: function (form, depistage_grossesse_id) {
        var form_depistage = getForm('edit_last_screenings');

        var url = new Url("maternite", "otherScreeningsAutocomplete");
        url.addParam("input_field", "depistage_grossesse_view");
        url.addParam("depistage_grossesse_id", depistage_grossesse_id);
        url.autoComplete(form.elements.depistage_grossesse_view, 'other_screenings_list', {
            minChars: 3,
            method: "get",
            dropdown: false,
            width: "300px",
            afterUpdateElement: function (field, selected) {
                var pathologie_name = selected.get('pathologie_name');
                $V(form_depistage.elements[pathologie_name], 1);
                $V(field, '');
            }
        });
    },
    /**
     * Remove the mother's pathology tag
     *
     * @param pathologie_name
     * @param dossier_perinatal_id
     */
    removePathologyTag: function (pathologie_name, dossier_perinatal_id) {
        if (confirm($T('CDossierPerinat-msg-Are you sure you want to remove this pathology'))) {
            var form_patho = getForm('edit_mother_pathologies');
            $V(form_patho.elements[pathologie_name], 0);
        }
    },

    verifyDateScreenings: function (form) {
        var depistages = [form.rubeole, form.toxoplasmose, form.syphilis, form.vih, form.hepatite_b, form.hepatite_c];
        var all_empty = true;
        depistages.forEach(function (depistage) {
            if (depistage.value !== "") {
                all_empty = false;
                document.getElementById("labelFor_edit_perinatal_folder__date_depistage").addClassName("notNull");
                document.getElementById("labelFor_edit_perinatal_folder__date_depistage").removeClassName("notNullOK");
            }
        });
        if (!form._date_depistage.value) {
            if (!all_empty) {
                document.getElementById("labelFor_edit_perinatal_folder__date_depistage").addClassName("notNull");
                document.getElementById("labelFor_edit_perinatal_folder__date_depistage").removeClassName("notNullOK");
            }
        }
        if (all_empty && document.getElementById("labelFor_edit_perinatal_folder__date_depistage").classList.contains("notNull")) {
            document.getElementById("labelFor_edit_perinatal_folder__date_depistage").removeClassName("notNull");
        }
        if (all_empty && document.getElementById("labelFor_edit_perinatal_folder__date_depistage").classList.contains("notNullOK")) {
            document.getElementById("labelFor_edit_perinatal_folder__date_depistage").removeClassName("notNullOK");
        }
        if (
            form._date_depistage.value
            && document.getElementById("labelFor_edit_perinatal_folder__date_depistage").classList.contains("notNull")
        ) {
            document.getElementById("labelFor_edit_perinatal_folder__date_depistage").addClassName("notNullOK");
            document.getElementById("labelFor_edit_perinatal_folder__date_depistage").removeClassName("notNull");
        }
    },

    addOrDeleteNaissanceReaPrat: function(form_name, naissance_id, naissance_rea_id = null, del = 0) {
      let url = new Url();
      url.addParam('@class', 'CNaissanceRea');
      url.addParam('naissance_rea_id', naissance_rea_id);
      url.addParam('del', del)

      if (!del) {
        let form = getForm(form_name);
        if (!$V(form.rea_par_id)) {
          return;
        }

        url.addParam('rea_par', $V(form.rea_par));
        url.addParam('rea_par_id', $V(form.rea_par_id));
      }

      url.addParam('naissance_id', naissance_id);
      url.requestUpdate('systemMsg', {method: 'POST', onComplete: () => {DossierMater.updateNaissanceReaPrat(naissance_id)}});
    },

    updateNaissanceReaPrat: function(naissance_id) {
      new Url('maternite', 'updateResuscitatorsList')
        .addParam('naissance_id', naissance_id)
        .requestUpdate('rea_list');
    },

    /**
     * Update provisional dates for the creation of a pregnancy
     */
    updateProvisionalDates: function () {
        const form   = getForm("editFormGrossesse"),
              inputs = {
                  "FIRST_ULTRASOUND_WEEK" : "estimate_first_ultrasound_date",
                  "SECOND_ULTRASOUND_WEEK": "estimate_second_ultrasound_date",
                  "THIRD_ULTRASOUND_WEEK" : "estimate_third_ultrasound_date",
              };

        if ($V(form.date_debut_grossesse) === '') {
            $("editFormGrossesse-provisional-dates").hide();

            for (let input in inputs) {
                $V(form[inputs[input]], "");
                $V(form[inputs[input] + "_da"], "");
            }

            $V(form.estimate_sick_leave_date, "");
            $V(form.estimate_sick_leave_date_da, "");
        } else {
            $("editFormGrossesse-provisional-dates").show();

            for (let input in inputs) {
                const date = new Date($V(form.date_debut_grossesse)).addDays(DossierMater[input]);

                $V(form[inputs[input]], date.toDATE());
                $V(form[inputs[input] + "_da"], date.toLocaleDate());
            }

            $V(form.estimate_sick_leave_date, new Date($V(form.date_debut_grossesse)).addDays(DossierMater.SICK_LEAVE_WEEK + 272).toDATE());
            $V(form.estimate_sick_leave_date_da, new Date($V(form.date_debut_grossesse)).addDays(DossierMater.SICK_LEAVE_WEEK + 272).toLocaleDate());
        }
    }
};
