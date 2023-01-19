{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{if !$object->_can->read}}
    <div class="small-info">
        {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{assign var="file" value=$object}}

<script>
    onCloseFile = function () {
        if (window.loadAllDocs) {
            loadAllDocs();
        }
        if (window.refreshListImages) {
            refreshListImages('{{$object->object_class}}-{{$object->object_id}}');
        }

        if (window.File) {
          File.refresh('{{$object->object_id}}', '{{$object->object_class}}');
        }
    };

    trashFile = function (form, file_view) {
        return confirmDeletion(form, {typeName: "le fichier", objName: file_view}, onCloseFile);
    };

    archiveFile = function (form) {
        if (confirm($T("CFile-comfirm_cancel"))) {
            $V(form.annule, 1);
            return onSubmitFormAjax(form, onCloseFile);
        }
    };

    restoreFile = function (form) {
        $V(form.annule, 0);
        return onSubmitFormAjax(form, onCloseFile);
    };

    moveFile = function (file_id) {
        new Url("files", "ajax_edit_file")
            .addParam("file_id", file_id)
            .requestModal(null, null, {onClose: onCloseFile});
    };

    renameFile = function (file_id) {
        new Url("files", "ajax_rename_file")
            .addParam("file_id", file_id)
            .requestModal(null, null, {onClose: onCloseFile});
    };
</script>

<table class="tbl">
    <tr>
        <th class="title text">
            {{mb_include module=files template="inc_file_synchro" docItem=$object}}

            {{mb_include module=system template=inc_object_idsante400}}
            {{mb_include module=system template=inc_object_history}}
            {{mb_include module=system template=inc_object_notes}}
            {{$object}}
        </th>
    </tr>
</table>

<table class="main">
    <tr>
        <td
          style="text-align: center; width: 66px;" {{* Ne pas utiliser la class narrow car déforme la tooltip (Chrome) *}}>
            <div style="width: 66px; height: 92px; background: white; cursor: pointer;"
                 onclick="new Url().ViewFilePopup('{{$file->object_class}}', '{{$file->object_id}}', 'CFile', '{{$file->_id}}')">
                {{thumbnail document=$file profile=medium style="max-width: 64px; max-height: 92px; border: 1px solid black; vertical-align: middle;"}}
            </div>
        </td>
        <td style="vertical-align: top;" class="text">
            {{foreach from=$object->_specs key=prop item=spec}}
                {{mb_include module=system template=inc_field_view}}
            {{/foreach}}
            {{if $file->_count_deliveries > 0}}
                {{mb_include module=compteRendu template=inc_deliveries object=$file}}
            {{/if}}
        </td>
    </tr>
    <tr>
        <td colspan="2" class="button">
            {{if $file->_can->edit}}
                {{if $file->file_type == "image/fabricjs"}}
                    <button type="button" class="edit"
                            onclick="editDrawing('{{$file->_id}}', null, null, window.loadAllDocs ? window.loadAllDocs : Prototype.emptyFunction);">{{tr}}Edit{{/tr}}</button>
                {{else}}
                    {{thumblink document=$file class="button download"}}{{tr}}Download{{/tr}}{{/thumblink}}
                {{/if}}
                <button type="button" class="hslip" onclick="moveFile('{{$file->_id}}');">{{tr}}Move{{/tr}}</button>
                <button type="button" class="edit"
                        onclick="renameFile('{{$file->_id}}')">{{tr}}CFile-_rename{{/tr}}</button>
                <form name="actionFile{{$file->_guid}}" method="post">
                    <input type="hidden" name="m" value="files"/>
                    <input type="hidden" name="dosql" value="do_file_aed"/>
                    {{mb_key object=$file}}
                    {{mb_field object=$file field=annule hidden=1}}

                    {{if $file->annule}}
                        <button type="button" class="undo"
                                onclick="restoreFile(this.form);">{{tr}}Restore{{/tr}}</button>
                    {{else}}
                        <button type="button" class="cancel"
                                onclick="archiveFile(this.form);">{{tr}}Cancel{{/tr}}</button>
                    {{/if}}
                    {{if $can->admin}}
                        <button type="button" class="trash"
                                onclick="trashFile(this.form, '{{$file|smarty:nodefaults|JSAttribute}}');">{{tr}}Delete{{/tr}}</button>
                    {{/if}}
                </form>
                <button type="button" class="fa fa-share-alt"
                        onclick="DocumentItem.viewRecipientsForSharing('{{$file->_guid}}')">{{tr}}Send{{/tr}}</button>
                {{if "dmp"|module_active}}
                    {{mb_include module=dmp template=inc_buttons_files_dmp _doc_item=$file}}
                {{/if}}

                {{if $app->user_prefs.useTAMMSIH && $object->_ref_object->_class !== "CEvenementPatient"}}
                    <button type="button" class="fa fa-share-alt"
                        onclick="DocumentItem.viewSharingWithSIH('{{$file->_guid}}')">{{tr}}CAppelSIH-Send to{{/tr}}</button>
                {{/if}}
            {{/if}}
        </td>
    </tr>
</table>
