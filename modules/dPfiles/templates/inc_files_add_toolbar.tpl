{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=canFile value=0}}
{{mb_default var=canDoc  value=0}}
{{mb_default var=mozaic  value=0}}

<div id="button_toolbar" class="me-button-toolbar">
  {{assign var=object_guid value="$object_class-$object_id"}}
  {{if $canFile && !$accordDossier}}
    <button style="float: left" class="new me-primary me-margin-top-8" type="button" onclick="uploadFile('{{$object_guid}}')">
     {{tr}}CFile-title-create{{/tr}}
    </button>

    {{if $mozaic}}
      <button style="float:left;" class="new me-tertiary me-margin-top-8" type="button" onclick="File.createMozaic('{{$object_guid}}', '', reloadAfterUploadFile);">{{tr}}CFile-create-mozaic{{/tr}}</button>
    {{/if}}

    {{if "drawing"|module_active}}
      <button style="float:left;" class="drawing me-tertiary me-margin-top-8" type="button" onclick="editDrawing(null, null, '{{$object_guid}}', reloadAfterUploadFile);">{{tr}}CDrawingItem.new{{/tr}}</button>
    {{/if}}

    {{if "mbHost"|module_active && $app->user_prefs.upload_mbhost}}
      {{mb_include module=mbHost template=inc_button_upload_file}}
    {{/if}}
    {{if $object && ($object->_nb_cancelled_files || $object->_nb_cancelled_docs)}}
      <button class="hslip me-tertiary me-dark me-margin-top-8" onclick="showCancelled(this)">Voir / Masquer {{math equation=x+y x=$object->_nb_cancelled_files y=$object->_nb_cancelled_docs}} fichier(s) annulé(s)</button>
    {{/if}}
  {{/if}}
  {{if $canDoc}}
    <div style="float: left" class="me-float-none me-padding-top-8"  id="document-add-{{$object_guid}}"></div>
    <script>
      Main.add(function() {
        Document.register('{{$object_id}}', '{{$object_class}}', '{{$praticienId}}', "document-add-{{$object_guid}}", "hide");
      });
    </script>
  {{/if}}

  <select name="order_docitems" style="margin-left: 10px;" onchange="reloadListFile(null, null, this.value);">
    <option value="nom"  {{if $order_docitems == "nom"}}selected{{/if}}>{{tr}}CDocumentItem-_order_nom{{/tr}}</option>
    <option value="date" {{if $order_docitems == "date"}}selected{{/if}}>{{tr}}CDocumentItem-_order_date{{/tr}}</option>
  </select>
</div>
