{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=unique_all_docs}}

{{mb_script module=patients script=fileviewer ajax=true}}

{{if "dmp"|module_active}}
    {{mb_script module=dmp script=cdmp ajax=true}}
{{/if}}

{{assign var=vue_globale_importance         value=$app->user_prefs.vue_globale_importance}}
{{assign var=vue_globale_cats               value=$app->user_prefs.vue_globale_cats}}
{{assign var=vue_globale_docs_prat          value=$app->user_prefs.vue_globale_docs_prat}}
{{assign var=vue_globale_docs_func          value=$app->user_prefs.vue_globale_docs_func}}
{{assign var=vue_globale_display_all_forms  value=$app->user_prefs.vue_globale_display_all_forms}}

<script>
    if (!window.all_docs_guids) {
        window.all_docs_guids = [];
    }

    window.all_docs_guids.push("{{$unique_all_docs}}");

    sortBy = function (order_col, order_way) {
        loadAllDocs(order_col, order_way);
    };

    loadAllDocs = function (order_col, order_way) {
        window.all_docs_guids.each(function (all_docs_guid) {
            window["loadAllDocs" + all_docs_guid](order_col, order_way);
        });
    };

    loadAllDocs{{$unique_all_docs}} = function (order_col, order_way) {
        var form = getForm("filterDisplay{{$unique_all_docs}}");
        if (!$("area_docs_{{$unique_all_docs}}") || !form) {
            return;
        }

        if (window.DHE && DHE.syncDocs) {
            var context_guid = $V(form.context_guid);
            var split = context_guid.split("-");
            DHE.syncDocs(split[0], split[1]);
        }

        new Url("patients", "ajax_all_docs")
            .addFormData(form)
            .addParam("cat_ids[]", $V(form["cat_ids[]"]).split('|'), 1)
            .addParam("unique_all_docs", "{{$unique_all_docs}}")
            .addNotNullParam('order_col', order_col)
            .addNotNullParam('order_way', order_way)
            .addNotNullParam('ondblclick', '{{if $ondblclick}}{{$ondblclick}}{{/if}}')
            .addNotNullParam('with_docs', '{{$with_docs}}')
            .addNotNullParam('with_files', '{{$with_files}}')
            .addNotNullParam('with_forms', '{{$with_forms}}')
            .requestUpdate("area_docs_{{$unique_all_docs}}", function () {
                getForm('filterDisplay{{$unique_all_docs}}').select('input.search[type="text"]')[0].onkeyup();
            });
    };

    toggleLabels{{$unique_all_docs}} = function () {
        var form = getForm("filterDisplay{{$unique_all_docs}}");
        var elts = form.elements["display"];
        var display = $V(form.display);

        $A(elts).each(function (elt) {
            var label = elt.up("label");
            if (elt.value === display) {
                label.removeClassName("opacity-30");
                label.addClassName("opacity-100");
            } else {
                label.removeClassName("opacity-100");
                label.addClassName("opacity-30");
            }
        });

        if ($("nb_element")) {
            var node = $("nb_element");
            var icon = $("element_icon");
            $("show_file_selected").removeChild(node);
            $("show_file_selected").removeChild(icon);
        }
    };

    filterResults{{$unique_all_docs}} = function (keywords) {
        var area_docs = $("area_docs_{{$unique_all_docs}}");
        var items = area_docs.select(".item_name");
        var display = getForm('filterDisplay{{$unique_all_docs}}').display.value;

        if (!keywords) {
            items.each(function (elt) {
                if (display === 'icon') {
                    elt.up("table").setStyle({display: "inline-table"});
                } else {
                    elt.up("tr").setStyle({display: "table-row"});
                }
            });
            return;
        }

        items.each(function (elt) {
            if (display === 'icon') {
                elt.up("table").setStyle({display: "none"});
            } else {
                elt.up("tr").setStyle({display: "none"});
            }
        });

        keywords = keywords.split(" ");

        area_docs.select(".item_name").each(function (item) {
            keywords.each(function (keyword) {
                if (item.getText().like(keyword)) {
                    if (display === 'icon') {
                        item.up("table").setStyle({display: "inline-table"});
                    } else {
                        item.up("tr").setStyle({display: "table-row"});
                    }
                }
            });
        });
    };

    getFileSelected = function () {
        $$('table.layout.table_icon_fileview').invoke("hide");
        var elements = $$('table.layout.table_icon_fileview');
        elements.each(function (elt) {
            elt.select("div").each(function (option) {
                if (option.hasClassName('file-selected')) {
                    elt.show();
                    // Agrandir les images
                    var src = elt.down("img").src.replace('w=64&h=92', 'w=164&h=192');
                    elt.down("img").src = src;
                    elt.down("img").setAttribute('style', 'background: white; width:164px; height:192px');
                    elt.down("div").setStyle({width: '164px', height: '192px'});

                    $("element_icon").setAttribute("onclick", "toggleLabels{{$unique_all_docs}}(); loadAllDocs{{$unique_all_docs}}();");
                    $("element_icon").setAttribute("title", "Voir tous les documents");
                }
            });
        });
    };

    toggleSelectFile = function (element) {
        if (element.hasClassName('file-selected')) {
            element.removeClassName('file-selected');
        } else {
            element = $(element);
            element.addClassName('file-selected');
        }

        var nb_selected = $$('div.file-selected').length;

        if ($("nb_element")) {
            var node = $("nb_element");
            var icon = $("element_icon");
            $("show_file_selected").removeChild(node);
            $("show_file_selected").removeChild(icon);
        }
        if (nb_selected > 0) {
            $("show_file_selected").insert(
                DOM.p({id: 'nb_element', style: 'display:inline-block'}, nb_selected + " élément(s) séléctionné(s)")
            );
            $("show_file_selected").insert(
                DOM.a({
                        id: 'element_icon',
                        title: 'Voir les documents sélectionnés',
                        style: 'display:inline-block; font-size: 13pt; ' +
                            'font-weight: normal; margin-left: 10px; cursor: pointer;' +
                            'margin-right: 5px;',
                        'onclick': 'getFileSelected();'
                    },
                    DOM.i({class: 'fas fa-list ', title: 'Voir les documents sélectionnés'}))
            );
        }
    };

    addCat = function (select, load) {
        var option = select.options[select.selectedIndex];

        select.selectedIndex = 0;

        var div = $("list_cats_{{$unique_all_docs}}");

        window.token_cats.add(option.value);

        if (Object.isUndefined(load)) {
            load = true;
        }

        savePref("vue_globale_cats", $V(select.form.elements['cat_ids[]']), function () {
            div.insert(DOM.button({
                className: "remove",
                "data-cat_id": option.value,
                onclick: "window.token_cats.remove(this.get('cat_id'));" +
                    "savePref('vue_globale_cats', $V(this.form.elements['cat_ids[]']), loadAllDocs);" +
                    "this.remove();"
            }, option.getText().trim()));
            if (load) {
                loadAllDocs();
            }
        });
    };

    toggleFilterOwner = function (button, input, value, load) {
        var border = "2px solid black";

        if (input.value) {
            value = border = "";
        }

        $V(input, value);
        button.setStyle({border: border});

        if (Object.isUndefined(load)) {
            load = true;
        }

        savePref(input.name === "user_id" ? "vue_globale_docs_prat" : "vue_globale_docs_func", value, load ? loadAllDocs : null);
    };

    savePref = function (pref, value, callback) {
        var form = getForm("editPref");

        if (!$V(form.user_id)) {
            return;
        }

        var input = DOM.input({type: "hidden", name: "pref[" + pref + "]", value: value});

        form.insert(input);

        onSubmitFormAjax(form, function () {
            input.remove();

            if (callback) {
                callback();
            }
        })
    };

    Main.add(function () {
        var form = getForm("filterDisplay{{$unique_all_docs}}");

        window.token_cats = new TokenField(form.elements["cat_ids[]"]);

        {{if $vue_globale_docs_prat}}
        toggleFilterOwner(form.down("button.user"), form.user_id, '{{$app->user_id}}', false);
        {{/if}}

        {{if $vue_globale_docs_func}}
        toggleFilterOwner(form.down("button.function"), form.function_id, '{{$app->_ref_user->function_id}}', false);
        {{/if}}

        window.token_cats.getValues().each(function (value) {
            var cat_deleted = false;
            var option = null;

            $V(form.categories, value, false);

            if (!$V(form.categories)) {
                cat_deleted = true;
                option = DOM.option({value: value}, 'Catégorie supprimée');
                form.categories.append(option);
                $V(form.categories, value, false);
            }

            addCat(form.categories, false);

            if (cat_deleted) {
                option.remove();
            }
        });

        loadAllDocs{{$unique_all_docs}}();
    });
</script>

<form name="editPref" method="post">
    <input type="hidden" name="m" value="admin"/>
    <input type="hidden" name="dosql" value="do_preference_aed"/>
    <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
</form>

{{if "dmp"|module_active}}
    {{mb_include module="dmp" template="inc_dossier_patient_dmp"}}
{{/if}}

<form name="filterDisplay{{$unique_all_docs}}" method="get" onsubmit="return false;">
    <input type="hidden" name="patient_id" value="{{$patient_id}}"/>
    <input type="hidden" name="context_guid" value="{{$context_guid}}"/>
    <input type="hidden" name="context_copy_guid" value="{{$context_copy_guid}}"/>
    <input type="hidden" name="type_doc" value="{{$type_doc}}"/>
    <input type="hidden" name="cat_ids[]" value="{{$vue_globale_cats}}"/>
    <table class="form me-margin-top-0">
        <tr>
            <th class="title" style="vertical-align: middle;" colspan="5">
                {{me_form_field field_class="me-form-icon search me-form-group-inline me-valign-middle"}}
                    <input type="text" style="float: left;" class="search"
                           onkeyup="filterResults{{$unique_all_docs}}(this.value)"/>
                {{/me_form_field}}
                <span id="show_file_selected"></span>

                <span style="float: right;">
          {{assign var=explode_object value="-"|explode:$context_guid}}
                    {{if $explode_object.0 === "CConsultation"}}
                        <button type="button" class="mail me-tertiary"
                                onclick="Document.sendDocuments('{{$explode_object.0}}', '{{$explode_object.1}}');">
              {{tr}}CDocumentItem-action-send{{/tr}}
            </button>


{{if 'apicrypt'|module_active}}
                        {{mb_script module=apicrypt script=Apicrypt ajax=true}}
                        <button type="button" class="mail-apicrypt me-tertiary"
                                onclick="Apicrypt.sendDocuments('{{$context_guid}}');">{{tr}}CCompteRendu.send_mail_apicrypt{{/tr}}</button>
                    {{/if}}
            {{if 'mssante'|module_active}}
                        {{mb_script module=mssante script=MSSante ajax=true}}
                        <button type="button" class="mail-mssante me-tertiary"
                                onclick="MSSante.viewSendDocuments('{{$context_guid}}');">{{tr}}CCompteRendu.send_mail_mssante{{/tr}}</button>
                    {{/if}}
                    {{/if}}
                    {{if "courrier"|module_active}}
                        {{mb_include module=courrier template=inc_button_publipostage}}
                    {{/if}}
                    {{if $type_doc !== "ordonnances"}}
                        {{mb_include module=patients template=inc_button_add_doc}}
                    {{/if}}
        </span>

                {{me_form_field field_class="me-form-group-inline me-valign-middle"}}
                    <select name="tri" onchange="loadAllDocs{{$unique_all_docs}}()">
                        <option
                          value="date" {{if $tri === "date"}} selected{{/if}}>{{tr}}CPatient-status.date{{/tr}}     </option>
                        {{if preg_match("/^CPatient/", $context_guid)}}
                        <option value="context"
                                {{if $tri === "context"}}selected{{/if}}>{{tr}}CPatient-status.context{{/tr}} </option>
                        {{/if}})
                        <option value="cat"
                                {{if $tri === "cat"}}selected{{/if}}>{{tr}}CPatient-status.category{{/tr}}</option>
                    </select>
                {{/me_form_field}}

                <label
                  style="font-family: FontAwesome; font-size: 13pt; font-weight: normal; margin-left: 10px; margin-right: 5px;"
                  class="{{if $display !== "icon"}}opacity-30{{/if}}">
                    <input type="radio" name="display" value="icon" {{if $display === "icon"}}checked{{/if}}
                           onclick="toggleLabels{{$unique_all_docs}}(); loadAllDocs{{$unique_all_docs}}(); DocumentV2.changeView('icon');"
                           style="display: none;"/> <i class="fas fa-th"></i>
                </label>

                <label
                  style="font-family: FontAwesome; font-size: 13pt; font-weight: normal; margin-left: 5px; margin-right: 10px;"
                  class="{{if $display !== "list"}}opacity-30{{/if}}">
                    <input type="radio" name="display" value="list" {{if $display === "list"}}checked{{/if}}
                           onclick="toggleLabels{{$unique_all_docs}}(); loadAllDocs{{$unique_all_docs}}(); DocumentV2.changeView('list');"
                           style="display: none;"/> <i class="fas fa-list"></i>
                </label>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <label class="me-margin-right-8">
                    <input type="radio" name="importance" value="high"
                           {{if $vue_globale_importance === "high"}}checked{{/if}}
                           onchange="savePref('vue_globale_importance', this.value, loadAllDocs)"/>
                    Importants
                </label>
                <label class="me-margin-right-8">
                    <input type="radio" name="importance" value="medical"
                           {{if $vue_globale_importance === "medical"}}checked{{/if}}
                           onchange="savePref('vue_globale_importance', this.value, loadAllDocs)"/>
                    Médicaux
                </label>
                <label>
                    <input type="radio" name="importance" value="" {{if !$vue_globale_importance}}checked{{/if}}
                           onchange="savePref('vue_globale_importance', this.value, loadAllDocs);"/>
                    Tous
                </label>
            </td>
            <th>
                Les miens uniquement
            </th>
            <td>
                <button type="button" class="user notext"
                        onclick="toggleFilterOwner(this, this.form.user_id, User.id);"></button>
                <input type="hidden" name="user_id"/>
            </td>
            <td class="narrow">
                <button class="hslip not-printable me-tertiary me-dark" style="float: right;" data-show=""
                        onclick="$$('.doc_canceled').invoke('toggle');">
                    {{tr}}CCompteRendu-Display hide canceled document|pl{{/tr}}
                </button>
            </td>
        </tr>
        <tr>
            {{me_form_field nb_cells=2 label="Category" field_class="me-form-group-inline"}}
                <select name="categories" onchange="addCat(this);">
                    <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                    {{foreach from=$categories item=_cat}}
                        <option value="{{$_cat->_id}}">{{$_cat}}</option>
                    {{/foreach}}
                </select>
                <div id="list_cats_{{$unique_all_docs}}" class="me-ws-nowrap">

                </div>
            {{/me_form_field}}
            <th>
                Ceux de mon cabinet uniquement
            </th>
            <td>
                <button type="button" class="function notext"
                        onclick="toggleFilterOwner(this, this.form.function_id, User.function.id);"></button>
                <input type="hidden" name="function_id"/>
            </td>
            <td class="narrow">
                <button class="hslip not-printable me-tertiary {{if !$vue_globale_display_all_forms}}me-dark{{/if}}" style="float: right;" data-show=""
                        onclick="DocumentV2.toogleAllForms(this, loadAllDocs)">
                    {{if !$vue_globale_display_all_forms}}
                        {{tr}}CCompteRendu-action-Display all forms|pl{{/tr}}
                    {{else}}
                        {{tr}}CCompteRendu-action-Hide additional forms|pl{{/tr}}
                    {{/if}}
                </button>
                <input id="display_all_forms" type="hidden" name="display_all_forms" value="{{$vue_globale_display_all_forms}}">
            </td>
        </tr>
    </table>
</form>

<div id="area_docs_{{$unique_all_docs}}" style="width: 100%; position: relative;"></div>
