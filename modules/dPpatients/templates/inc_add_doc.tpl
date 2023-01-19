{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi       script=modele_etiquette ajax=true}}
{{mb_script module=files       script=file             ajax=true}}
{{mb_script module=compteRendu script=document         ajax=true}}
{{mb_script module=compteRendu script=modele_selector  ajax=true}}
{{mb_script module=patients script=documentV2 ajax=true}}
{{mb_script module=ccam script=DevisCodage ajax=1}}


{{assign var=self_guid value="$context->_class-$context->_id"}}
{{assign var=self_guid value=$self_guid|md5}}
{{assign var=self_guid value="guid_$self_guid"}}
{{unique_id var=cerfa_guid}}

<script>
    changeContext = function () {
        new Url("patients", "ajax_context_doc")
            .addParam("patient_id", "{{$patient->_id}}")
            .requestModal("60%", "60%");
    };

    // Refresh de l'explorateur apr�s ajout d'un fichier
    reloadAfterUpload = function () {
        Control.Modal.close();
        refreshAfterAdd();
    };

    // Callback de refresh
    refreshAfterAdd = function () {
        if (window.loadAllDocs) {
            loadAllDocs();
        }
        if (window.DocumentV2) {
            var selector = printf("div.documentsV2-%s-%s", "{{$context->_class}}", "{{$context->_id}}");
            $$(selector).each(function (elt) {
                DocumentV2.refresh(elt);
            });
        }
    };

    // Refresh de l'explorateur apr�s ajout d'un formulaire
    ExObject.refreshSelf['{{$self_guid}}'] = refreshAfterAdd;

    //Open cerfa
    editCerfa = function (name, object_class, object_id) {
        var url = new Url("cerfa", "vw_cerfa_edit");
        url.addParam("cerfa", name);
        url.addParam("object_class", object_class);
        url.addParam("object_id", object_id);
        url.pop(900, 900, "Cerfa " + name);
    };

    Main.add(function () {

        DevisCodage.list('{{$context->_class}}', '{{$context->_id}}');

        Document.ext_cabinet_id = '{{$ext_cabinet_id}}';
        File.ext_cabinet_id = '{{$ext_cabinet_id}}';

        var form = getForm("addDoc-{{$context->_guid}}");

        var urlModele = new Url("compteRendu", "autocomplete");
        urlModele.addParam("user_id", "{{$prat->_id}}");
        urlModele.addParam("object_class", '{{$context->_class}}');
        urlModele.addParam("object_id", '{{$context->_id}}');
        urlModele.autoComplete(form.keywords_modele, '', {
            method:             "get",
            minChars:           2,
            afterUpdateElement: function (input, selected) {
                Control.Modal.close();
                Document.createDocAutocomplete('{{$context->_class}}', '{{$context->_id}}', '', input, selected);
            },
            dropdown:           true,
            width:              "250px"
        });

        var urlPack = new Url("compteRendu", "ajax_pack_autocomplete");
        urlPack.addParam("user_id", "{{$prat->_id}}");
        urlPack.addParam("function_id", "{{$prat->function_id}}");
        urlPack.addParam("object_class", '{{$context->_class}}');
        urlPack.addParam("object_id", '{{$context->_id}}');
        urlPack.autoComplete(form.keywords_pack, '', {
            minChars:           2,
            afterUpdateElement: function (input, selected) {
                Control.Modal.close();
                Document.createPackAutocomplete('{{$context->_class}}', '{{$context->_id}}', '', input, selected);
            },
            dropdown:           true,
            width:              "250px"
        });

        modeleSelector[{{$context->_id}}] = new ModeleSelector("addDoc-{{$context->_guid}}", null, "_modele_id", "_object_id", "_fast_edit");

        {{if "ameli"|module_active}}
        //Liste des Cerfas
        var form_cerfa = getForm("form-{{$cerfa_guid}}-{{$context->_guid}}");

        var url = new Url("cerfa", "ajax_cerfa_autocomplete");
        var origText = $V(form_cerfa.keywords);

        url.autoComplete(form_cerfa.keywords, '', {
            minChars:           3,
            dropdown:           true,
            width:              "250px",
            afterUpdateElement: function (input, selected) {
                var name = selected.get("name");
                $V(input, origText);

                editCerfa(name, '{{$context->_class}}', '{{$context->_id}}');
            }
        });
        {{/if}}
    });
</script>

<table class="main">
    <tr>
        <th colspan="2">
            <h2>
                <strong>
                    {{tr}}CPatient-Contexte{{/tr}} :
                    {{if $context->_class == "CPatient"}}
                        {{tr}}CPatient{{/tr}} {{$context}}
                    {{elseif $context->_class == "CSejour"}}
                        {{$context}}
                        &mdash;
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$context->_ref_praticien}}
                    {{elseif $context->_class == "CConsultation"}}
                        {{tr var1=$context->_date|date_format:$conf.date}}dPcabinet-Consultation of %s{{/tr}} {{tr}}To{{/tr}} {{$context->heure|date_format:$conf.time}}
                        &mdash;
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$context->_ref_chir}}
                    {{elseif $context->_class == "CConsultAnesth"}}
                        {{tr var1=$context->_ref_consultation->_date|date_format:$conf.date}}dPcabinet-Consultation of %s{{/tr}} {{tr}}To{{/tr}} {{$context->_ref_consultation->heure|date_format:$conf.time}}
                        &mdash;
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$context->_ref_chir}}
                    {{elseif $context->_class == "COperation"}}
                        {{$context}}
                        &mdash;
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$context->_ref_chir}}
                    {{elseif $context->_class == "CEvenementPatient"}}
                        {{$context}}
                        &mdash;
                        {{mb_value object=$context field=date}}
                    {{else}}
                        {{$context}}
                    {{/if}}
                    <button type="button" class="change notext" onclick="changeContext();"></button>
                </strong>
            </h2>
        </th>
    </tr>
</table>

<fieldset class="case_add_doc">
    <legend>{{tr}}CTimelineCabinet-Document{{/tr}}</legend>

    <form name="addDoc-{{$context->_guid}}" method="post">
        <input type="hidden" name="_fast_edit" value=""/>
        <input type="hidden" name="_modele_id" value=""/>
        <input type="hidden" name="_object_id" value=""
               onchange="var fast_edit = $V(this.form._fast_edit);
                 var modele_id = $V(this.form._modele_id);
                 var _object_id = $V(this);
                 Control.Modal.close();
                 if (fast_edit == '1') {
                 Document.fastMode('{{$context->_class}}', modele_id, '{{$context->_id}}');
                 }
                 else {
                 Document.create(modele_id, _object_id, '{{$context->_id}}', '{{$context->_class}}');
                 }"/>
        <table class="main">
            <tr>
                <td class="halfpane">
                    <input type="text" placeholder="&mdash; {{tr}}CCompteRendu-modele-one{{/tr}}" name="keywords_modele"
                           class="autocomplete str"
                           autocomplete="off"
                           style="font-size: 13pt; background-position: 100% 4px;"/>
                </td>
                <td rowspan="2" class="button">
                    <button type="button" class="search big" style="width: 100px;"
                            onclick="modeleSelector[{{$context->_id}}].pop('{{$context->_id}}','{{$context->_class}}','{{$curr_user->_id}}');"></button>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" placeholder="&mdash; {{tr}}CPack{{/tr}}" name="keywords_pack"
                           class="autocomplete str" autocomplete="off"
                           style="font-size: 13pt; background-position: 100% 4px;"/>
                </td>
            </tr>
        </table>
    </form>
</fieldset>

<fieldset id="drop_file_area" class="case_add_doc">
    <legend>{{tr}}File{{/tr}}</legend>

    <div style="text-align: center;">
        {{assign var=category value=null}}
        {{if $context->_class === "CEvenementPatient" && 'oxCabinet'|module_active}}
            {{assign var=category value=$categorie}}
        {{/if}}
        <button type="button" class="new big"
                onclick="uploadFile('{{$context->_guid}}', {{if $category}} '{{$category}}' {{else}} null {{/if}}, null,
    null, null, '{{$ext_cabinet_id}}');">
            {{tr}}common-action-Add file{{/tr}}
        </button>
    </div>
</fieldset>

{{if $ex_classes_creation|@count}}
    <fieldset class="case_add_doc">
        <legend>{{tr}}CExClass{{/tr}}</legend>
        <div style="text-align: center;">
            <select onchange="Control.Modal.close(); ExObject.showExClassFormSelect(this, '{{$self_guid}}')"
                    style="width: 80%; font-size: 13pt;">
                <option value=""> &ndash; {{tr}}CExClass.new_in{{/tr}} {{$context}} </option>
                {{foreach from=$ex_classes_creation item=_ex_class_events key=_ex_class_id}}
                    {{if $_ex_class_events|@count > 1}}
                        <optgroup label="{{$ex_classes.$_ex_class_id}}">
                            {{foreach from=$_ex_class_events item=_ex_class_event}}
                                <option value="{{$_ex_class_event->ex_class_id}}"
                                        data-reference_class="{{$context->_class}}"
                                        data-reference_id="{{$context->_id}}"
                                        data-host_class="{{$_ex_class_event->host_class}}"
                                        data-event_name="{{$_ex_class_event->event_name}}">
                                    {{$_ex_class_event}}
                                </option>
                            {{/foreach}}

                        </optgroup>
                    {{else}}
                        {{foreach from=$_ex_class_events item=_ex_class_event}}
                            <option value="{{$_ex_class_event->ex_class_id}}"
                                    data-reference_class="{{$context->_class}}"
                                    data-reference_id="{{$context->_id}}"
                                    data-host_class="{{$_ex_class_event->host_class}}"
                                    data-event_name="{{$_ex_class_event->event_name}}">
                                {{$ex_classes.$_ex_class_id}}
                            </option>
                        {{/foreach}}
                    {{/if}}
                {{/foreach}}
            </select>
        </div>
    </fieldset>
{{/if}}

<fieldset class="case_add_doc">
    <legend>{{tr}}CFile-create-mozaic{{/tr}}</legend>

    <div style="text-align: center;">
        <button class="big new" style="width: 100px;"
                onclick="File.createMozaic('{{$context->_guid}}');"></button>
    </div>
</fieldset>

{{if "drawing"|module_active}}
    <fieldset class="case_add_doc">
        <legend>{{tr}}CDrawingItem.new{{/tr}}</legend>

        <div style="text-align: center;">
            <button class="big drawing" style="width: 100px;"
                    onclick="editDrawing(null, null, '{{$context->_guid}}');"></button>
        </div>
    </fieldset>
{{/if}}

{{if $context->_class == "CSejour"}}
    <fieldset class="case_add_doc">
        <legend>{{tr}}CModeleEtiquette-court{{/tr}}</legend>

        <div style="text-align: center;">
            <button class="big modele_etiquette" style="width: 100px;"
                    onclick="ModeleEtiquette.chooseModele('{{$context->_class}}', '{{$context->_id}}', Control.Modal.close)"></button>
        </div>
    </fieldset>
{{/if}}

{{if "ameli"|module_active}}
    <fieldset class="case_add_doc">
        <legend>{{tr}}mod-cerfa-tab-show_cerfa{{/tr}}</legend>
        <form name="form-{{$cerfa_guid}}-{{$context->_guid}}" method="get" onsubmit="return false">
            <table class="main">
                <tr>
                    <td class="halfpane">
                        <input type="text" placeholder="&mdash; {{tr}}Cerfa{{/tr}}" name="keywords" size="8"
                               class="autocomplete str listCerfa"
                               style="font-size: 13pt; background-position: 100% 4px; width: 215px;"/>
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>
{{/if}}

{{if $context->_class != "CPatient" && $context->_class != 'CEvenementPatient'}}
    <fieldset class="case_add_doc">
        <legend>{{tr}}CDevisCodage{{/tr}}</legend>
        <div id="view-devis"></div>
    </fieldset>
{{/if}}
