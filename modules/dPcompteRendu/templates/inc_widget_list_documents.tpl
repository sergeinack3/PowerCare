{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

<script>
  Main.add(function() {
    if (window.updateCountTab) {
      updateCountTab();
    }
  });
</script>

{{mb_default var=object_class value=$object->_class}}
{{mb_default var=object_id value=$object->_id}}
{{unique_id var=unique_id_toolbar}}

{{if $mode != "hide"}}

  {{foreach from=$affichageDocs item=_cat key=_cat_id}}
    {{assign var=docCount value=$_cat.items|@count}}
      <script>
        Main.add(function() {
          Control.Tabs.setTabCount("Category-documents-{{$unique_id}}-{{$object->_class}}-{{$_cat_id}}", '{{$docCount}}');
        });
      </script>
    <tbody id="Category-documents-{{$unique_id}}-{{$object->_class}}-{{$_cat_id}}" class="docs_container_{{$_cat_id}} me-no-border" style="display: none; clear: both;">
      {{foreach from=$_cat.items item=document}}
        <tr {{if $document->annule}}style="display: none;" class="doc_cancelled hatching"{{/if}}>
          <td class="text docitem">
            {{if $document->_can->read}}
              <a href="#{{$document->_guid}}" onclick="Document.edit({{$document->_id}}, null, null, '{{$unique_id}}'); return false;" style="display: inline;">
            {{/if}}
            {{if $document->_is_locked}}
              <i class="me-icon lock me-primary" onmouseover="ObjectTooltip.createEx(this, '{{$document->_guid}}', 'locker')"></i>
            {{/if}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$document->_guid}}')">
                {{$document}}
              </span>
            {{if $document->_can->read}}
              </a>
            {{/if}}
            {{if $document->private}}
              &mdash; <em>{{tr}}CCompteRendu-private{{/tr}}</em>
            {{/if}}
          </td>

          <td>
            {{mb_include module=files template=inc_file_synchro docItem=$document}}
            {{mb_include module=files template=inc_file_send docItem=$document}}
          </td>

          <td class="button" style="width: 1px; white-space: nowrap;">
            <form name="Edit-{{$document->_guid}}" action="?m={{$m}}" method="post">
              <input type="hidden" name="m" value="compteRendu" />
              <input type="hidden" name="dosql" value="do_modele_aed" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="annule" value="0" />
              {{mb_key object=$document}}

              <input type="hidden" name="object_id" value="{{$object_id}}" />
              <input type="hidden" name="object_class" value="{{$object_class}}" />
              <input type="hidden" name="file_category_id" value="{{$document->file_category_id}}" />
              <button type="button" class="print notext not-printable me-tertiary me-dark"
                      onclick="{{if $app->user_prefs.pdf_and_thumbs}}
              Document.printPDF({{$document->_id}}, {{if $document->signature_mandatory}}1{{else}}0{{/if}}, {{if $document->valide}}1{{else}}0{{/if}});
            {{else}}
              Document.print({{$document->_id}});
            {{/if}}">
                {{tr}}Print{{/tr}}
              </button>
              {{if $document->_can->edit && !$document->_is_locked}}
                {{if !$document->annule}}
                  <button class="cancel notext compact me-tertiary me-dark" type="button" onclick="Document.cancel(this.form)">
                    {{tr}}Cancel{{/tr}}
                  </button>
                {{else}}
                  <button class="undo notext compact me-tertiary me-dark" type="button" onclick="Document.restore(this.form)">
                    {{tr}}Restore{{/tr}}
                  </button>
                {{/if}}
                <button type="button" class="trash notext not-printable me-tertiary me-dark" onclick="Document.del(this.form, '{{$document->nom|smarty:nodefaults|JSAttribute}}', '{{$unique_id}}')">
                  {{tr}}Delete{{/tr}}
                </button>
              {{/if}}
            </form>

            {{assign var=category_id value=$document->file_category_id}}
            {{assign var=onComplete value="Document.refreshList.curry('$category_id', '$object_class', '$object_id')"}}
            <button type="button" class="fa fa-share-alt notext me-tertiary" onclick="DocumentItem.viewRecipientsForSharing('{{$document->_guid}}', {{$onComplete}})">{{tr}}Send{{/tr}}</button>

            {{if "dmp"|module_active}}
              {{mb_include module=dmp template=inc_buttons_files_dmp _doc_item=$document}}
            {{/if}}
          </td>

          {{if "dPfiles CDocumentSender system_sender"|gconf}}
            <td class="button" style="width: 1px">
              <form name="Send-{{$document->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
                <input type="hidden" name="m" value="compteRendu" />
                <input type="hidden" name="dosql" value="do_modele_aed" />
                <input type="hidden" name="del" value="0" />
                {{mb_key object=$document}}

                <!-- Send File -->
                {{mb_include module=files template=inc_file_send_button
                _doc_item=$document
                notext=notext
                onComplete="Document.refreshList('$document->file_category_id', '$object_class','$object_id')"
                }}
              </form>
            </td>
          {{/if}}
        </tr>
      {{/foreach}}
    </tbody>
    {{foreachelse}}
      <tr>
        <td colspan="3" class="empty">
          {{tr}}{{$object->_class}}{{/tr}} : {{tr}}CMbObject-back-documents.empty{{/tr}}
        </td>
      </tr>
  {{/foreach}}
{{/if}}
