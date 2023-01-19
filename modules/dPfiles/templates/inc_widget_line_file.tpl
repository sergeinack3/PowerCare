{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{unique_id var=unique_id}}

{{mb_default var=alerts_anormal_result value=false}}
{{mb_default var=alerts_new_result value=false}}

<table class="form me-no-align me-no-box-shadow">
  <tr {{if $_file->annule}}style="display: none;" class="file_cancelled hatching"{{/if}}>
    <td class="text docitem me-padding-0">
      <a href="#" class="action" id="readonly_{{$_file->_guid}}"
         onclick="File.popup('{{$object_class}}','{{$object_id}}','{{$_file->_class}}','{{$_file->_id}}');"
         onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}', 'objectView')">{{$_file}}</a>

      <!--  Formulaire pour modifier le nom d'un fichier -->
      <form name="editName-{{$_file->_guid}}" method="post"
        onsubmit="if (File.checkFileName($V(this.file_name))) {
            return onSubmitFormAjax(this, File.reloadFile.curry('{{$object_id}}', '{{$object_class}}', '{{$_file->_id}}'));
        }
        return false;">
        {{mb_key object=$_file}}
        <input type="hidden" name="m" value="files" />
        <input type="hidden" name="dosql" value="do_file_aed" />
        <input type="text" style="display: none;" name="file_name" size="50" value="{{$_file->file_name}}" />
        <script>
          var form = getForm("editName-{{$_file->_guid}}");
          var evt = Prototype.Browser.Gecko ? "keypress" : "keydown";
          Event.observe(form.file_name, evt, File.switchFile.curry('{{$_file->_id}}', form));
        </script>
        <span id="buttons_{{$_file->_guid}}" style="display: none;">
          <button class="tick notext compact" type="button"
            onclick="if (File.checkFileName($V(this.form.file_name))) { this.form.onsubmit(); }">{{tr}}Valid{{/tr}}</button>
        </span>
      </form>
      <small>({{$_file->_file_size}})</small>
      {{if $_file->private}}
        &mdash; <em>{{tr}}CCompteRendu-private{{/tr}}</em>
      {{/if}}
    </td>

    <td>
      {{mb_include module=files template=inc_file_synchro docItem=$_file}}
    </td>
    {{if ($alerts_anormal_result || $alerts_new_result) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
      <td style="width: 0px">
        {{assign var=file_id value=$_file->_id}}
        {{if array_key_exists($file_id, $alerts_anormal_result)}}
          <span id="OxLaboAlert_{{$file_id}}">
            {{mb_include module=oxLaboClient template=vw_alerts object=$object object_id=$object->_id object_class=$object->_class response_id=$file_id response_type='file' nb_alerts=$alerts_anormal_result.$file_id.total alerts=$alerts_anormal_result.$file_id}}
          </span>
        {{/if}}
        {{if array_key_exists($file_id, $alerts_new_result)}}
          <span id="OxLaboNewAlert_{{$file_id}}">
            {{mb_include module=oxLaboClient template=vw_alerts object=$object object_id=$object->_id object_class=$object->_class response_id=$file_id response_type='file' nb_alerts=$alerts_new_result.$file_id|@count alerts=$alerts_new_result.$file_id alert_new_result=true}}
          </span>
        {{/if}}
      </td>
    {{/if}}

    {{if $_file->_can->edit}}

      <td class="button me-padding-0" style="width: 1px">
        <form name="Delete-{{$_file->_guid}}" method="post" onsubmit="return checkForm(this)">
          <input type="hidden" name="m" value="files" />
          <input type="hidden" name="dosql" value="do_file_aed" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="annule" value="0" />
          {{mb_key object=$_file}}
          {{mb_field object=$_file field="_view" hidden=1}}
          <span style="white-space: nowrap;">

            {{if !$name_readonly}}
              {{me_button label=Modify icon=edit old_class="compact notext" onclick="File.editNom('`$_file->_guid`')"}}
            {{/if}}

            {{thumblink document=$_file class="button print notext compact me-tertiary"}}{{/thumblink}}

            {{if !$_file->annule}}
              {{me_button label=Cancel icon=cancel old_class="compact notext" onclick="File.cancel(this.form, '`$object_id`', '`$object_class`', '`$_file->file_category_id`')"}}
            {{else}}
              {{me_button label=Restore icon=undo old_class="compact notext" onclick="File.restore(this.form, '`$object_id`', '`$object_class`', '`$_file->file_category_id`')"}}
            {{/if}}

            {{if $can->admin}}
              {{me_button label=Delete icon=trash old_class="compact notext" onclick="File.remove(this, '`$object_id`', '`$object_class`')"}}
            {{/if}}

            {{me_button label=Send icon="fa fa-share-alt" old_class=notext
                        onclick="DocumentItem.viewRecipientsForSharing('`$_file->_guid`', File.refresh.curry('`$object_id`', '`$object_class`'))"}}

            {{if "dmp"|module_active}}
              {{mb_include module=dmp template=inc_buttons_files_dmp _doc_item=$_file notext=true}}
            {{/if}}

            {{me_dropdown_button button_label=Options button_icon=opt button_class="notext me-tertiary me-dark"
                                  container_class="me-dropdown-button-right" }}
          </span>
        </form>

        {{assign var=onComplete value="File.refresh.curry('$object_id','$object_class')"}}
      </td>
    {{/if}}

    {{if "dPfiles CDocumentSender system_sender"|gconf}}
      <td class="button me-padding-0" style="width: 1px">
        <form name="Edit-{{$_file->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">

        <input type="hidden" name="m" value="files" />
        <input type="hidden" name="dosql" value="do_file_aed" />
        <input type="hidden" name="callback" value="reloadCallback">
        <input type="hidden" name="del" value="0" />
        {{mb_key object=$_file}}

        <!-- Send File -->
        {{mb_include module=files template=inc_file_send_button
           notext=notext
            _doc_item=$_file
            onComplete="File.refresh.curry('$object_id','$object_class')"
        }}
        </form>
      </td>
    {{/if}}
  </tr>
</table>
