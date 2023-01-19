{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

<script>
  requestInfoPat = function(pat_id, dossier_id) {
    var url = new Url("patients", "ajax_radio_last_refs");

    var oForm = getForm("editFrm");
    if (pat_id) {
      url.addParam("patient_id", pat_id);
    }
    else {
      if(!oForm.patient_id.value) {
        if ($V(oForm._nda)) {
          url.addParam('nda', $V(oForm._nda));
        }
        else {
          return false;
        }
      }
      url.addElement(oForm.patient_id);
    }

    if(dossier_id) {
      url.addParam("dossier_id", dossier_id);
    }

    url.addElement(oForm.consultation_id);
    url.requestUpdate("recherche_patient");
    return true;
  };

  attach = {
  objects: [],
  files: "",
  plain:null,
  html:null,
  rename_text:null,
  category_id:null,

  setObject: function(checkbox, object_guid) {
    if (checkbox.checked) {
      this.objects.push(object_guid);
    } else if (this.objects.indexOf(object_guid) !== -1) {
      this.objects.splice(this.objects.indexOf(object_guid), 1);
    }
  }
};

  checkrelation = function() {
    attach.files = "";
    attach.plain = null;
    attach.html = null;
    $$(".check input:checked").each(function(data) {
      if (attach.files !="") {
        attach.files = attach.files+"-";
      }
      attach.files = attach.files+data.value;
    });

    var aform = getForm("select_attach");

    if ($$(".plain input:checked").length > 0) {
      attach.plain = aform.attach_plain.value;
      attach.rename_text = aform.rename_text.value;
      attach.category_id = aform.category_id.value;
    }

    if ($$(".html input:checked").length > 0) {
      attach.html = aform.attach_html.value;
      attach.rename_text = aform.rename_text.value;
      attach.category_id = aform.category_id.value;
    }

    if (attach.objects.length && (attach.files.length > 0 || attach.plain || attach.html)) {
      $("do_link_attachments").enable();
    } else {
      $("do_link_attachments").disable();
    }
  };

  Main.add(function () {
    UserEmail.listAttachLink('{{$mail_id}}', 1);
    {{if $patient->_id}}
      requestInfoPat('{{$patient->_id}}','{{$dossier_id}}');
    {{/if}}

    var form = getForm('editFrm');
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CPatient");
    url.addParam("field", "patient_id");
    url.addParam("view_field", "_patient_view");
    url.addParam("input_field", "_seek_patient");
    {{if $function_distinct && !$app->_ref_user->isAdmin()}}
      {{if $function_distinct == 1}}
        url.addParam("where[function_id]", "{{$app->_ref_user->function_id}}");
      {{else}}
        url.addParam("where[group_id]", "{{$g}}");
      {{/if}}
    {{/if}}
    url.autoComplete(form.elements._seek_patient, null, {
      minChars: 3,
      method: "get",
      select: "view",
      dropdown: false,
      width: "300px",
      afterUpdateElement: function(field, selected) {
        $V(field.form.patient_id, selected.get("guid").split("-")[1]);
        $V(field.form.elements._pat_name, selected.down('.view').innerHTML);
        $V(field.form.elements._seek_patient, "");
        attach.objects = [];
      }
    });
  });
</script>

<style>
  #linkAttachment img{
    max-width: 100px;
    max-height: 100px;
  }

  #linkAttachment li{
    list-style: none;
  }
</style>

<table class="main" id="linkAttachment">
  <tr><th colspan="2" class="title">Lier à un dossier</th></tr>
  {{if $mail->is_apicrypt || $mail->_is_hprim}}
    <tr>
      <td colspan="2">
        <div class="small-info">
          {{if $patient->_id}}
            {{tr}}CUserMail-msg-patient_link_detected{{/tr}}
          {{else}}
            {{tr}}CUserMail-msg-patient_link_undetected{{/tr}} {{$last_name}} {{$first_name}} ({{$birth}})
          {{/if}}
        </div>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td colspan="2" class="button">
      <button id="do_link_attachments"{{if !$dossier_id}}disabled{{/if}} onclick="UserEmail.dolinkAttachment(attach, '{{$mail_id}}')">
        <i class="msgicon fa fa-link"></i>
        {{tr}}Lier{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td style="width:50%;" id="list_attachments">
    </td>
    <td style="width:50%;">
      <form class="watched prepared" method="post" action="?m=dPcabinet" name="editFrm" autocomplete="off" novalidate="on">
        {{mb_field object=$patient field="patient_id" hidden=1 ondblclick="PatSelector.init()" onchange="requestInfoPat();"}}
        <table class="form">
          <tr>
            <th rowspan="2">
              Patient
            </th>
            <td>
              <input type="text" name="_pat_name" style="width: 15em;" value="{{$patient}}" readonly="readonly" onclick="PatSelector.init()" />
              <button class="search notext" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
              <script type="text/javascript">
                PatSelector.init = function(){
                  this.sForm      = "editFrm";
                  this.sId        = "patient_id";
                  this.sView      = "_pat_name";
                  var seekResult  = $V(getForm(this.sForm)._seek_patient).split(" ");
                  this.sName      = seekResult[0] ? seekResult[0] : "";
                  this.sFirstName = seekResult[1] ? seekResult[1] : "";
                  this.pop();
                }
              </script>
              <button id="button-edit-patient" type="button"
                      onclick="location.href='?m=patients&tab=vw_edit_patients&patient_id='+this.form.patient_id.value"
                      class="edit notext" {{if !$patient->_id}}style="display: none;"{{/if}}>
              {{tr}}Edit{{/tr}}
              </button>
            </td>
          </tr>
          <tr>
            <td>
              <input type="text" name="_seek_patient" style="width: 13em;" placeholder="{{tr}}fast-search{{/tr}}" class="autocomplete" onblur="$V(this, '')" />
            </td>
          </tr>
          <tr>
            <th>
              <label for="_nda">NDA</label>
            </th>
            <td>
              <input type="text" name="_nda" value="">
              <button type="button" class="search notext" onclick="requestInfoPat();">{{tr}}Search{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>

      <div id="recherche_patient"></div>
    </td>
  </tr>
</table>
