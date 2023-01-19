{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=no_edit value=0}}

{{if !$fileSel || !$fileSel->_id}}
    <div class="small-info">
        {{tr}}CFile-select_document_preview{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{assign var="href" value="?m=files&dialog=preview_files&popup=1&objectClass=$objectClass&objectId=$objectId"}}

<script>
    window.onbeforeunload = function () {
    };

    openWindowMail = function () {
        new Url("compteRendu", "ajax_view_mail")
            .addParam("object_guid", "{{$fileSel->_guid}}")
            .requestModal(700, '90%');
    };

    modifTitle = function () {
        $("fileName").hide();
        $("modifTitlefrm").show();
    };

    updatetitle = function () {
        var form = getForm('frm-file-name');
        $("fileName").update($V(form.file_name)).show();
        $("modifTitlefrm").hide();
    };

    onSubmitRotate = function (element, rotate) {
        var form = element.form;
        $V(form._rotate, rotate);
        return onSubmitFormAjax(form, function () {
            document.location.reload()
        });
    };

    {{if $embed != 'null'}}
    embedContent = function (prevNext) {
      new Url('files', 'preview_files')
        .addParam('sfn', prevNext)
        .addParam('nonavig', '{{$nonavig}}')
        .addParam('objectClass', '{{$objectClass}}')
        .addParam('objectId', '{{$objectId}}')
        .addParam('elementClass', '{{$elementClass}}')
        .addParam('elementId', '{{$elementId}}')
        .addParam('popup', '1')
        .addParam('no_buttons', '1')
        .addParam('embed', '{{$embed}}')
        .requestUpdate('{{$embed}}');
    };
    {{else}}
    goToPage = function (numpage) {
      window.location.href = "?m=files&dialog=preview_files&popup=1&objectClass={{$objectClass}}&objectId={{$objectId}}&elementClass={{$elementClass}}&elementId={{$elementId}}&nonavig={{$nonavig}}&sfn=" + numpage;
    };
    {{/if}}

    Main.add(function () {
        {{if "porteDocuments"|module_active}}
        Folder.view_light = {{if $view_light}}1{{else}}0{{/if}};
        {{/if}}
    });

</script>

<table class="main form">
    <tr>
        <td>
            <div style="text-align: center;">
                {{if !$nonavig && ($filePrev || $fileNext) && !$no_buttons}}
                    <a class="button left  {{if !$filePrev}}disabled{{/if}}" style="float: left;"
                       {{if $filePrev}}href="{{$href}}&elementClass={{$filePrev.elementClass}}&elementId={{$filePrev.elementId}}"{{/if}}>
                        {{tr}}CDocumentItem-prev{{/tr}}
                    </a>
                    <a class="button right {{if !$fileNext}}disabled{{/if}}" style="float: right;"
                       {{if $fileNext}}href="{{$href}}&elementClass={{$fileNext.elementClass}}&elementId={{$fileNext.elementId}}"{{/if}}>
                        {{tr}}CDocumentItem-next{{/tr}}
                    </a>
                {{/if}}

                <!-- Nom du fichier -->
                <strong {{if $elementClass == "CFile"}}onclick="modifTitle(this)"{{/if}}>
                    <span id="fileName">{{$fileSel}}</span>
                    {{if $fileSel->private}}
                        &mdash;
                        <em>{{tr}}CCompteRendu-private{{/tr}}</em>
                    {{/if}}
                </strong>

                {{if $elementClass == "CFile"}}
                    <div id="modifTitlefrm" style="display: none">
                        {{mb_form name="frm-file-name" m="files" method="post" onsubmit="return onSubmitFormAjax(this, updatetitle);"}}
                        {{mb_class object=$fileSel}}
                        {{mb_key object=$fileSel}}
                        {{mb_field object=$fileSel field=file_name}}
                            <button type="submit" class="save notext">{{tr}}Save{{/tr}}</button>
                        {{/mb_form}}
                    </div>
                {{/if}}
                <!-- Category -->
                {{if $catFileSel->nom}}
                    {{if $inline_header}}
                        -
                    {{else}}
                        <br/>
                    {{/if}}
                    {{mb_label object=$fileSel field=file_category_id}} :
                    {{$catFileSel->nom}}
                {{/if}}

                <!-- Date -->
                {{if $fileSel->_class == "CFile"}}
                    {{if $inline_header}}
                        -
                    {{else}}
                        <br/>
                    {{/if}}
                    {{mb_label object=$fileSel field=file_date}} : {{mb_value object=$fileSel field=file_date}}
                {{/if}}

                <br/>
                <a class="button notext thumbnails"
                   href="?m=files&dialog=ajax_files_gallery&object_class={{$objectClass}}&object_id={{$objectId}}"></a>


                <form name="editMasquage" method="post" onsubmit="return onSubmitFormAjax(this);">
                  {{mb_class object=$fileSel}}
                  {{mb_key object=$fileSel}}
                  {{mb_include module=files template=inc_button_masquage docitem=$fileSel}}
                </form>

                {{if $exchange_source->_id && !$no_buttons}}
                    <button type="button" class="mail"
                            onclick="openWindowMail();">{{tr}}CCompteRendu.send_mail{{/tr}}</button>
                {{/if}}

                {{if 'apicrypt'|module_active && !$no_buttons}}
                    {{mb_script module=apicrypt script=Apicrypt}}
                    <button type="button" class="mail-apicrypt"
                            onclick="Apicrypt.sendDocument('{{$fileSel->object_class}}-{{$fileSel->object_id}}', '{{$fileSel->_guid}}');">{{tr}}CCompteRendu.send_mail_apicrypt{{/tr}}</button>
                {{/if}}
                {{if 'mssante'|module_active && !$no_buttons}}
                    {{mb_script module=mssante script=MSSante ajax=true}}
                    <button type="button" class="mail-mssante"
                            onclick="MSSante.viewSendDocument('{{$fileSel->object_class}}-{{$fileSel->object_id}}', '{{$fileSel->_guid}}');">{{tr}}CCompteRendu.send_mail_mssante{{/tr}}</button>
                {{/if}}

                {{thumblink class="button download" document=$fileSel}}{{tr}}Download{{/tr}}{{/thumblink}}
                {{if $fileSel|instanceof:'Ox\Mediboard\Files\CFile' && ($fileSel->file_type == 'application/osoft' || $fileSel->file_type == 'text/osoft')}}
                    {{thumblink class="button download" document=$fileSel download_raw=1}}{{tr}}Download-raw{{/tr}}{{/thumblink}}
                {{/if}}

                {{if "porteDocuments"|module_active && $objectClass == "CFolder" && $object->_ref_folder_ack_for_user->_id}}
                    {{mb_script module=porteDocuments script=folder ajax=true}}

                    {{assign var=no_edit value=1}}
                    {{assign var=folder_ack_user value=$object->_ref_folder_ack_for_user}}
                    <form name="folderAckUser_{{$folder_ack_user->_id}}" method="post">
                        {{mb_key object=$folder_ack_user}}
                        {{mb_class object=$folder_ack_user}}
                        <input type="hidden" name="user_id" value="{{$app->_ref_user->_id}}"/>
                        <input type="hidden" name="folder_id" value="{{$object->_id}}"/>
                        <input type="hidden" name="datetime" value=""/>
                        <input type="hidden" name="del" value="0"/>

                        <button type="button" onclick="Folder.confirmDeleteFolderAck(this.form, '{{$object->_id}}');">
                            <i class="fas fa-eye-slash"></i> {{tr}}CFolderAck-action-Mark as unread{{/tr}}
                        </button>
                    </form>
                {{/if}}

                {{if $fileSel|instanceof:'Ox\Mediboard\Files\CFile' && $fileSel->_can->edit && !$no_buttons}}
                    {{mb_form name="send-file" m="files" method="post" onsubmit="return onSubmitFormAjax(this);"}}
                    {{mb_class object=$fileSel}}
                    {{mb_key object=$fileSel}}
                    {{mb_label object=$fileSel field=send typeEnum=checkbox}}
                    {{mb_field object=$fileSel field=send typeEnum=checkbox onchange="this.form.onsubmit();"}}
                    {{/mb_form}}
                {{/if}}
            </div>
        </td>
    </tr>
</table>

{{assign var=title value=$app->tr('CFile.download')}}

<div style="text-align: center;">
    {{if $file_list}}

        {{tr}}dPfiles-msg-File in zip|pl{{/tr}} :

        {{thumblink document=$fileSel title="$title"}}
            <ul>
                {{foreach from=$file_list item=_file_path}}
                    <li>{{$_file_path}}</li>
                {{/foreach}}
            </ul>
        {{/thumblink}}
    {{else}}
        {{if $fileSel->_class == "CFile" && !$includeInfosFile && !$no_edit}}
            <form name="FileRotate" method="post">
                <input type="hidden" name="m" value="files"/>
                <input type="hidden" name="dosql" value="do_file_aed"/>
                <input type="hidden" name="callback" value="reloadCallback">
                {{mb_key object=$fileSel}}
                {{mb_field object=$fileSel field=_rotate hidden=1}}

                <button type="button" style="float: left;" class="rotate_left notext singleclick"
                        onclick="onSubmitRotate(this, 'left')" title="{{tr}}CFile._rotate.left{{/tr}}"></button>
                <button type="button" style="float: right;" class="rotate_right notext singleclick"
                        onclick="onSubmitRotate(this, 'right')" title="{{tr}}CFile._rotate.right{{/tr}}"></button>
            </form>
        {{/if}}

        {{if $fileSel->_class == "CFile" && $fileSel->_nb_pages > 1}}
            <a class="button left {{if $page_prev === null}}disabled{{/if}}"
               {{if $embed != 'null'}}onclick="embedContent({{$page_prev|default:'0'}});"{{/if}}
                    {{if $page_prev !== null && $embed == 'null'}}href="{{$href}}&elementClass={{$elementClass}}&elementId={{$elementId}}&nonavig={{$nonavig}}&sfn={{$page_prev}}"{{/if}}>
                Page précédente
            </a>
            {{if $fileSel->_nb_pages && $fileSel->_nb_pages >= 2}}
                <select name="_num_page"
                        onchange="{{if $embed != 'null'}}embedContent(document.getElementsByName('_num_page')[0].value);{{else}}goToPage(this.value);{{/if}}">
                    {{foreach from=$arrNumPages|smarty:nodefaults item=currPage}}
                        <option value="{{$currPage-1}}" {{if $currPage-1 == $sfn}}selected{{/if}}>
                            Page {{$currPage}} / {{$fileSel->_nb_pages}}
                        </option>
                    {{/foreach}}
                </select>
            {{elseif $fileSel->_nb_pages}}
                Page {{$sfn+1}} / {{$fileSel->_nb_pages}}
            {{/if}}
            <a class="button right rtl {{if $page_next === null}}disabled{{/if}}"
               {{if $embed != 'null'}}onclick="embedContent({{$page_next}});"{{/if}}
                    {{if $page_next !== null && $embed == 'null'}}href="{{$href}}&elementClass={{$elementClass}}&elementId={{$elementId}}&nonavig={{$nonavig}}&sfn={{$page_next}}"{{/if}}>
                Page suivante
            </a>
        {{/if}}
        <br/>
        {{if $includeInfosFile}}
            <div style="text-align: left;">
                {{mb_include module=files template=inc_preview_contenu_file}}
            </div>
        {{else}}
            {{assign var=page value=$sfn+1}}

            {{thumblink document=$fileSel title="$title"}}
            {{if $page > 1}}
                {{thumbnail id=thumb document=$fileSel profile=large page=$page style="border: 1px solid #000;"}}
            {{else}}
                {{thumbnail id=thumb document=$fileSel profile=large style="border: 1px solid #000;"}}
            {{/if}}
            {{/thumblink}}
        {{/if}}
    {{/if}}
</div>

{{*
{{if $isConverted == 1}}
  <a class="button save" target="_blank" href="?m=files&raw=fileviewer&file_id={{$file_id_original}}">{{tr}}CFile.save_original{{/tr}}</a>
{{/if}}
*}}
