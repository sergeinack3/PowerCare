{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{if $accordDossier}}
  {{mb_return}}
{{/if}}

{{if !$_doc_item->_can->read}}
  {{mb_return}}
{{/if}}

{{unique_id var=unique_id}}

{{if $_doc_item->_class=="CCompteRendu"}}
  <!-- Modification -->
    <button class="edit {{$notext}}" type="button" onclick="Document.edit({{$elementId}})">
      {{tr}}Edit{{/tr}}
    </button>
{{elseif $_doc_item->_class=="CFile"}}
  {{if $_doc_item->file_type == "image/fabricjs"}}
    <button class="edit {{$notext}}" type="button" onclick="editDrawing({{$elementId}}, null, null, reloadAfterUploadFile);">
      {{tr}}Edit{{/tr}}
    </button>
  {{else}}
    <button class="edit {{$notext}}" type="button" onclick="renameFile({{$elementId}}, '{{$_doc_item->file_category_id}}');">
      {{tr}}Edit{{/tr}}
    </button>

    <!-- Téléchargement du fichier -->
    {{assign var=title value=$app->tr('CFile.download')}}
    {{thumblink document=$_doc_item title="$title" class="button download notext"}}{{/thumblink}}
  {{/if}}
{{/if}}

<!-- Impression -->
{{if $_doc_item->_class=="CCompteRendu"}}
  {{if $_doc_item->valide}}
    {{assign var=valide value="1"}}
  {{else}}
    {{assign var=valide value="0"}}
  {{/if}}

  {{if $_doc_item->signature_mandatory}}
    {{assign var=signature value="1"}}
  {{else}}
    {{assign var=signature value="0"}}
  {{/if}}

  {{if $app->user_prefs.pdf_and_thumbs}}
    {{assign var=callback value="Document.printPDF('`$_doc_item->_id`', $signature, $valide);"}}
  {{else}}
      {{assign var=callback value="Document.print('`$_doc_item->_id`');"}}
  {{/if}}

  {{me_button label="Print" icon="print notext" onclick=$callback}}
{{/if}}

{{if !$_doc_item->_can->edit}}
  {{me_dropdown_button button_label=Action button_icon=opt button_class="notext me-tertiary"
  container_class="me-dropdown-button-right"}}
  {{mb_return}}
{{/if}}

<!-- Deletion -->
{{if $_doc_item->_class == "CCompteRendu"}}
  <form name="editDoc{{$_doc_item->_id}}" method="post">
    <input type="hidden" name="m" value="compteRendu" />
    <input type="hidden" name="dosql" value="do_modele_aed" />
    {{mb_key object=$_doc_item}}
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="annule" value="0" />
    {{assign var="confirmDeleteType" value="le document"}}
    {{assign var="confirmDeleteName" value=$_doc_item->nom|smarty:nodefaults|JSAttribute}}
{{elseif $_doc_item->_class == "CFile"}}
  <form name="editFile{{$_doc_item->_id}}" method="post">
    <input type="hidden" name="m" value="files" />
    <input type="hidden" name="dosql" value="do_file_aed" />
    {{mb_key object=$_doc_item}}
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="annule" value="0" />
    {{assign var="confirmDeleteType" value="le fichier"}}
    {{assign var="confirmDeleteName" value=$_doc_item->file_name|smarty:nodefaults|JSAttribute}}
{{/if}}

{{if $can->admin || ($_doc_item|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu' && !$_doc_item->_is_locked)}}
  <!-- Deletion -->
  {{me_button label="Delete" icon="trash" old_class="$notext"
  onclick="file_deleted=$elementId;confirmDeletion(this.form, {typeName:'$confirmDeleteType',objName:'$confirmDeleteName', ajax:1, target:'systemMsg'},reloadAfterDeleteFile.curry('`$_doc_item->file_category_id`'));"}}
{{/if}}

{{if $_doc_item->annule == "0"}}
  {{me_button label="Cancel" icon="cancel notext" onclick="cancelFile(this.form, '`$_doc_item->file_category_id`')"}}
{{else}}
  {{me_button label="Restore" icon="undo notext" onclick="restoreFile(this.form, '`$_doc_item->file_category_id`')"}}
{{/if}}

<!-- Move -->
{{me_button label="Move" icon="hslip" old_class="$notext" onclick="this.form.file_category_id.setVisibility(true)"}}

{{assign var=extensions_authorized_for_cda value='Ox\Mediboard\Files\CDocumentItem'|static:"extensions_authorized_for_cda"}}
{{if "cda"|module_active && $_doc_item->object_class !== 'CPatient' && (($_doc_item|instanceof:'Ox\Mediboard\Files\CFile' && $_doc_item->file_type|in_array:$extensions_authorized_for_cda) || $_doc_item|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu')}}
    {{if !$_doc_item->type_doc_dmp}}
        {{assign var=onclick value="DocumentItem.addTypeDocDMP('`$_doc_item->_guid`')"}}
    {{else}}
        {{assign var=onclick value="DocumentItem.generateCDA('`$_doc_item->_guid`')"}}
    {{/if}}

    {{me_button label="CDA-msg-Generate" icon="fas fa-file-code" old_class="$notext" onclick=$onclick}}
{{/if}}

{{if $_doc_item|instanceof:'Ox\Mediboard\Files\CFile' && (preg_match("#IHE\sXDM#", $_doc_item->_view) && $_doc_item->file_type == "application/zip")
    && $_doc_item->type_doc_dmp == "1.2.250.1.213.1.1.4.12^SYNTH"}}
    {{mb_script module=MSSante script=MSSante ajax=true}}
    {{assign var=onclick value="MSSante.viewSendVSM('`$_doc_item->_id`')"}}
    {{me_button label="CCDAEvent-msg-Send VSM" icon="fas fa-paper-plane" old_class="$notext" onclick=$onclick}}
{{/if}}

<!-- Share -->
{{assign var=doc_class   value=$_doc_item->_class}}
{{assign var=doc_id      value=$_doc_item->_id   }}
{{assign var=category_id value=$_doc_item->file_category_id}}
{{assign var=onComplete  value="Document.refreshList.curry('$category_id')"}}

<button type="button" class="fa fa-share-alt notext me-primary"
        onclick="DocumentItem.viewRecipientsForSharing('{{$_doc_item->_guid}}', {{$onComplete}})">{{tr}}Send{{/tr}}</button>

{{if "dmp"|module_active}}
  {{mb_include module=dmp template=inc_buttons_files_dmp}}
{{/if}}


{{me_dropdown_button button_label=Action button_icon=opt button_class="notext me-tertiary"
container_class="me-dropdown-button-right"}}

<br class="me-display-none" />
<br class="me-display-none"/>
<br />
<select style="visibility: hidden; width: 12em;" name="file_category_id" onchange="submitFileChangt(this.form)">
  <option value="" {{if !$_doc_item->file_category_id}}selected{{/if}}>&mdash; {{tr}}CFilesCategory.none{{/tr}}</option>
  {{foreach from=$listCategory item=curr_cat}}
    <option value="{{$curr_cat->file_category_id}}" {{if $curr_cat->file_category_id == $_doc_item->file_category_id}}selected{{/if}}>
      {{$curr_cat->nom}}
    </option>
  {{/foreach}}
</select>
</form>
