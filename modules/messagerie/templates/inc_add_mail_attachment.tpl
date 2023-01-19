{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  var count_files = 0;

  showLoading = function(){
    var systemMsg = $('systemMsg');
    systemMsg.update('\<div class=\'loading\'\>{{tr}}Loading in progress{{/tr}}\</div\>');
    systemMsg.show();
  }

  addFile = function(elt) {
    count_files ++;
    // Incrément du rowspan pour le th du label
    var label = $('fileLabel');
    label.writeAttribute('rowspan', count_files + 1);

    // Ajout d'un input pour le fichier suivant
    // <input type="file" name="formfile[0]" size="0" onchange="addFile(this); this.onchange=''"/>
    var tr = $('fileRow');
    tr.id = ''
    tr.insert({
        after: DOM.tr({id: 'fileRow'},
          DOM.td({colspan: 4},
            DOM.input({type: 'file', name: 'attachment[' + count_files + ']', size: 0, onchange: 'addFile(this); this.onchange=""'})
          )
        )
      }
    );
  }
</script>

<iframe name="upload-{{$attachment->mail_id}}" id="upload-{{$attachment->mail_id}}" style="display: none;"></iframe>

<form name="addAttachment" method="post" action="?" enctype="multipart/form-data" onsubmit="return checkForm(this);" target="upload-{{$attachment->mail_id}}">
  <input type="hidden" name="m" value="messagerie" />
  <input type="hidden" name="dosql" value="do_mail_attachment_aed" />
  <input type="hidden" name="ajax" value="1" />
  <input type="hidden" name="suppressHeaders" value="1" />
  <input type="hidden" name="callback" value="callbackModalMessagerie" />

  {{mb_class object=$attachment}}
  {{mb_key object=$attachment}}

  {{mb_field object=$attachment field=mail_id hidden=1}}

  <table class="form">
    <tr>
      <th colspan="2" class="title">
        {{tr}}CMailAttachments-title-add{{/tr}}
      </th>
    </tr>
    <tr>
      <td colspan="2">
         <div class="small-info">
          <span>{{tr}}config-dPfiles-General-upload_max_filesize{{/tr}} : <strong>{{"dPfiles General upload_max_filesize"|gconf}}</strong></span>
        </div>
      </td>
    </tr>
    <tr id="fileRow">
      <th id="fileLabel">
        <label>{{tr}}CFile{{/tr}}</label>
      </th>
      <td>
        <input type="file" name="attachment[0]" size="0" onchange="addFile(this); this.onchange=''"/>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button id="btn_add_attachments" type="submit" class="add" title="{{tr}}Add{{/tr}}" onclick="showLoading();">
          {{tr}}Add{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>