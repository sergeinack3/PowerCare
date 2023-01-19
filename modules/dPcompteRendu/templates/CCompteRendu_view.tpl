{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
    <div class="small-info">
        {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{mb_script module=compteRendu script=document ajax=1}}
{{mb_script module=files script=document_item ajax=true}}

{{assign var=document value=$object}}

<script>
    trashDoc = function (form, file_view) {
        return confirmDeletion(form, {typeName: "le document", objName: file_view}, function () {
            if (window.loadAllDocs) {
                loadAllDocs();
            }
        });
    };

    archiveDoc = function (form) {
        if (confirm($T("CFile-comfirm_cancel"))) {
            $V(form.annule, 1);
            return onSubmitFormAjax(form, function () {
                if (window.loadAllDocs) {
                    loadAllDocs();
                }
            });
        }
    };

    restoreDoc = function (form) {
        $V(form.annule, 0);
        return onSubmitFormAjax(form, function () {
            if (window.loadAllDocs) {
                loadAllDocs();
            }
        });
    };
</script>
<table class="tbl">
    <tr>
        <th class="title text">
            {{mb_include module=files template="inc_file_send" docItem=$document}}
            {{mb_include module=files template="inc_file_synchro" docItem=$document}}
            {{mb_include module=system template=inc_object_idsante400}}
            {{mb_include module=system template=inc_object_history}}
            {{mb_include module=system template=inc_object_notes}}
            {{$object}}
        </th>
    </tr>
</table>

<table class="main">
    <tr>
        {{assign var=file value=$document->_ref_file}}
        {{if $document->object_id && $app->user_prefs.pdf_and_thumbs && $file->_id}}
            <td id="thumbnail-{{$document->_id}}" style="text-align: center; width: 66px;">
                <a href="#1"
                   onclick="new Url().ViewFilePopup('{{$document->object_class}}', '{{$document->object_id}}', '{{$document->_class}}', '{{$document->_id}}')">
                    {{thumbnail document=$document profile=medium style="max-width: 64px; max-height: 92px; border: 1px solid black;"}}
                </a>
            </td>
        {{else}}
            <td style="text-align: center; width: 66px;">
                <img src="images/pictures/medifile.png"/>
            </td>
        {{/if}}

        <td style="vertical-align: top;" class="text">
            {{foreach from=$object->_specs key=prop item=spec}}
                {{mb_include module=system template=inc_field_view}}
            {{/foreach}}
            <strong>{{mb_label class=CFile field=_file_size}}</strong> : {{mb_value object=$file field=_file_size}}
            <br/>
          <strong><label>{{tr}}CCompteRendu.count_words{{/tr}}</label></strong> : {{$document->_source|count_words}}
            {{if $document->_ref_last_statut_compte_rendu->_id}}
              <br/>
              <strong><label>{{tr}}CStatutCompteRendu-statut{{/tr}}</label> </strong>:
                {{mb_value object=$document->_ref_last_statut_compte_rendu field=statut}}
            {{/if}}
            {{if $document->_count_deliveries > 0 || $document->_ref_file->_count_deliveries > 0}}
                {{mb_include module=compteRendu template=inc_deliveries object=$document}}
            {{/if}}
        </td>
    </tr>

    <tr>
        <td class="button" colspan="2">
            {{if $document->_can->edit}}
                {{if !$document->object_id}}
                    <a class="button search" href="?m=compteRendu&tab=vw_modeles&compte_rendu_id={{$document->_id}}">
                        {{tr}}Open{{/tr}}
                    </a>
                {{else}}
                    <button type="button" class="edit"
                            onclick="Document.edit('{{$document->_id}}')">{{tr}}Edit{{/tr}}</button>
                    <button type="button" class="print" onclick="
                    {{if $app->user_prefs.pdf_and_thumbs}}
                      Document.printPDF('{{$document->_id}}', {{if $document->signature_mandatory}}1{{else}}0{{/if}}, {{if $document->valide}}1{{else}}0{{/if}});
                    {{else}}
                      Document.print('{{$document->_id}}');
                    {{/if}}">{{tr}}Print{{/tr}}</button>
                    <form name="actionDoc{{$document->_guid}}" method="post">
                        <input type="hidden" name="m" value="compteRendu"/>
                        <input type="hidden" name="dosql" value="do_modele_aed"/>
                        {{mb_key object=$document}}
                        {{mb_field object=$document field=annule hidden=1}}

                        {{if $document->annule}}
                            <button type="button" class="undo"
                                    onclick="restoreDoc(this.form)">{{tr}}Restore{{/tr}}</button>
                        {{else}}
                            <button type="button" class="cancel"
                                    onclick="archiveDoc(this.form)">{{tr}}Cancel{{/tr}}</button>
                        {{/if}}
                        <button type="button" class="trash"
                                onclick="trashDoc(this.form, '{{$document->_view|JSAttribute|smarty:nodefaults}}')">{{tr}}Delete{{/tr}}</button>
                    </form>
                {{/if}}
                {{if $document->_ref_last_statut_compte_rendu}}
                    <button type="button" class="search" onclick="Document.showAllStatut({{$document->_id}})">Statuts</button>
                {{/if}}
                <button type="button" class="fa fa-share-alt"
                        onclick="DocumentItem.viewRecipientsForSharing('{{$document->_guid}}')">{{tr}}Send{{/tr}}</button>
                {{if "dmp"|module_active}}
                    {{mb_include module=dmp template=inc_buttons_files_dmp _doc_item=$document}}
                {{/if}}

                {{if $app->user_prefs.useTAMMSIH && $object->_ref_object->_class !== "CEvenementPatient"}}
                    <button type="button" class="fa fa-share-alt"
                        onclick="DocumentItem.viewSharingWithSIH('{{$document->_guid}}')">{{tr}}CAppelSIH-Send to{{/tr}}</button>
                {{/if}}
            {{/if}}
        </td>
    </tr>
</table>
