{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=modele_etiquette ajax=true}}
{{assign var=object_class value=$object->_class}}
{{assign var=object_id value=$object->_id}}
{{assign var=doc_count value=$object->_ref_documents|@count}}
{{unique_id var=unique_id}}

<script>
    Main.add(function () {
        Control.Tabs.create("tabs-documents-{{$unique_id}}-{{$object->_class}}", true);
    })
</script>

{{if $can_create_docs}}
    <script>
        Main.add(function () {
            ObjectTooltip.modes.locker = {
                module: "compteRendu",
                action: "ajax_show_locker",
                sClass: "tooltip"
            };

            var form = getForm('DocumentAdd-{{$unique_id}}-{{$object->_guid}}');
            var url;

            url = new Url("compteRendu", "autocomplete");
            url.addParam("user_id", "{{$praticien->_id}}");
            url.addParam("function_id", "{{$praticien->function_id}}");
            url.addParam("object_class", '{{$object_class}}');
            url.addParam("object_id", '{{$object_id}}');
            url.autoComplete(form.keywords_modele, '', {
                method: "get",
                minChars: 2,
                afterUpdateElement: Document.createDocAutocomplete.curry('{{$object_class}}', '{{$object_id}}', '{{$unique_id}}'),
                dropdown: true,
                width: "250px"
            });

            url = new Url("compteRendu", "ajax_pack_autocomplete");
            url.addParam("user_id", "{{$praticien->_id}}");
            url.addParam("function_id", "{{$praticien->function_id}}");
            url.addParam("object_class", '{{$object_class}}');
            url.addParam("object_id", '{{$object_id}}');
            url.autoComplete(form.keywords_pack, '', {
                minChars: 2,
                afterUpdateElement: Document.createPackAutocomplete.curry('{{$object_class}}', '{{$object_id}}', '{{$unique_id}}'),
                dropdown: true,
                width: "250px"
            });

            ModeleEtiquette.nb_printers = {{$nb_printers|@json}};
        });

        // Création via ModeleSelector
        modeleSelector[{{$object_id}}] = new ModeleSelector("DocumentAdd-{{$unique_id}}-{{$object->_guid}}", null, "_modele_id", "_object_id", "_fast_edit");
    </script>
    <form name="DocumentAdd-{{$unique_id}}-{{$object->_guid}}" action="?m={{$m}}" method="post"
          class="prepared not-printable">
        {{if $can_create_docs}}
            <input type="text" placeholder="&mdash; {{tr}}CModeleToPack-modele_id{{/tr}}" name="keywords_modele"
                   class="autocomplete str" autocomplete="off" style="width: 5em;"/>
            <input type="text" placeholder="&mdash; {{tr}}CPack{{/tr}}" name="keywords_pack" class="autocomplete str"
                   autocomplete="off" style="width: 4em;"/>
            <button type="button" class="search notext me-tertiary"
                    onclick="modeleSelector[{{$object_id}}].pop('{{$object_id}}','{{$object_class}}','{{$praticien->_id}}')">
                {{if $praticien->_can->edit}}
                    {{tr}}common-all|pl{{/tr}}
                {{else}}
                    {{tr}}CModeleToPack.disponible|pl{{/tr}}
                {{/if}}
            </button>
        {{/if}}

        <!-- Impression de tous les modèles disponibles pour l'objet -->
        <button type="button" class="print notext me-tertiary me-dark"
                onclick="Document.printSelDocs('{{$object_id}}', '{{$object_class}}');">
            {{tr}}CCompteRendu.global_print{{/tr}}
        </button>

        <input type="hidden" name="_fast_edit" value=""/>
        <input type="hidden" name="_modele_id" value=""/>
        <input type="hidden" name="_object_id" value=""
               onchange="var fast_edit = $V(this.form._fast_edit);
                 if (fast_edit == '1') {
                 Document.fastMode('{{$object_class}}', this.form._modele_id.value, '{{$object_id}}');
                 }
                 else {
                 Document.create(this.form._modele_id.value, this.value,'{{$object_id}}','{{$object_class}}');
                 }
                 $V(this, '', false);
                 $V(this.form._fast_edit, '');
                 $V(this.form._modele_id, '');"/>
    </form>
{{/if}}

{{if $nb_modeles_etiquettes > 0}}
    <button type="button" class="modele_etiquette not-printable me-tertiary me-dark"
            {{if $nb_modeles_etiquettes == 1}}
        onclick="ModeleEtiquette.print('{{$object_class}}', '{{$object_id}}')"
            {{else}}
        onclick="ModeleEtiquette.chooseModele('{{$object_class}}', '{{$object_id}}')"
            {{/if}}>{{tr}}CModeleEtiquette-short{{/tr}}</button>
{{/if}}

{{if "cerfa"|module_active}}
    {{mb_include module=cerfa template=inc_chose_cerfa object=$object}}
{{/if}}

{{if $object->_nb_cancelled_docs && $mode != "hide"}}
    <button class="hslip not-printable me-tertiary me-dark" style="float: right;" data-show=""
            onclick="Document.showCancelled(this)">
        {{tr var1=$object->_nb_cancelled_docs}}CCompteRendu-toggle_display_cancelled_docs{{/tr}}
    </button>
{{/if}}

{{if $can_create_docs && $can->admin}}
    <form name="DeleteAll-{{$object->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)"
          class="not-printable">
        <input type="hidden" name="m" value="dPcompteRendu"/>
        <input type="hidden" name="dosql" value="do_compte_rendu_multi_delete"/>
        <input type="hidden" name="del" value="1"/>
        <input type="hidden" name="object_guid" value="{{$object->_guid}}">

        <button class="trash me-tertiary me-dark" type="button" style="float: right;"
                onclick="Document.removeAll(this, '{{$object->_guid}}')">
            {{tr}}Delete-all{{/tr}}
        </button>
    </form>
{{/if}}

{{if $doc_count && $mode == "collapse"}}
    <div>
        <table class="form me-no-border-radius-bottom me-margin-0 me-no-box-shadow">
            <tr id="DocsEffect-{{$object->_guid}}-{{$unique_id}}-trigger">
                <th class="category me-text-align-left" colspan="3">
                    {{tr}}{{$object->_class}}{{/tr}} :
                    {{$doc_count}} document(s)
                    <script>
                        Main.add(function () {
                            new PairEffect("DocsEffect-{{$object->_guid}}-{{$unique_id}}", {
                                bStoreInCookie: true
                            });
                        });
                    </script>
                </th>
            </tr>
        </table>
    </div>
{{/if}}

<div id="DocsEffect-{{$object->_guid}}-{{$unique_id}}"
     {{if $mode == "collapse" && $doc_count}}style="display: none;"{{/if}}>
    <table class="form" id="docs_{{$object_class}}{{$object_id}}">
        {{if $mode != "hide"}}
            <tr>
                <td class="text me-padding-0" colspan="4">
                    {{if $affichageDocs}}
                        <ul id="tabs-documents-{{$unique_id}}-{{$object->_class}}"
                            class="control_tabs small me-border-only-bottom">
                            {{foreach from=$affichageDocs item=_cat key=_cat_id}}
                                {{assign var=docCount value=$_cat.items|@count}}
                                <li>
                                    <a href="#Category-documents-{{$unique_id}}-{{$object->_class}}-{{$_cat_id}}">
                                        {{$_cat.name}}
                                        <small>({{$docCount}})</small>
                                    </a>
                                </li>
                            {{/foreach}}
                        </ul>
                    {{/if}}
                </td>
            </tr>
        {{/if}}

        {{mb_include module="compteRendu" template="inc_widget_list_documents"}}
    </table>
</div>
