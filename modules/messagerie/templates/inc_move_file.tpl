{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=pat_selector ajax=1}}
{{mb_script module=dPfiles script=files ajax=1}}

<script>
  onsubmitMoveFile = function(form) {
    if (confirm("Êtes vous sur de vouloir lier ce fichier à ce dossier ?")) {
      getForm("editDocument").onsubmit();

      //move the file
      File_Attach.doMovefile(null, null, null, null, $V(form.file_name), $V(form.category_id));
    }
    Control.Modal.close();
  };

  requestInfoPat = function(patient_id) {
    var oform = getForm("moveFile_{{$file->_id}}");
    var _patient_id = patient_id ? patient_id : $V(oform.patient_id);
    var prat_id = $V(oform._prat_id);
    var date_guess = $V(oform._guess_date);
    if (_patient_id) {
      File_Attach.listRefsForPatient(_patient_id, prat_id, date_guess, "resultSearch");
    }
  };

  Main.add(function () {
    File_Attach.setFile('{{$file->_id}}', '{{$file->_class}}');
    File_Attach.button_Attach = "save_button";
    {{if $patient->_id}}
      requestInfoPat('{{$patient->_id}}');
    {{/if}}

    var form = getForm("moveFile_{{$file->_id}}");
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CPatient");
    url.addParam("field", "patient_id");
    url.addParam("view_field", "_patient_view");
    url.addParam("input_field", "_seek_patient");
    url.autoComplete(form.elements._seek_patient, null, {
      minChars: 3,
      method: "get",
      select: "view",
      dropdown: false,
      width: "300px",
      afterUpdateElement: function(field,selected){
        requestInfoPat(selected.get('id'));
      }
    });
  });

  Main.add(function() {
    ViewPort.SetAvlHeight("main_move_file", 1);
  });
</script>


<form method="post" name="editDocument" onsubmit="onSubmitFormAjax(this)">
  {{mb_class object=$document}}
  {{mb_key object=$document}}
  {{if $document->_class == "CBioserveurDocument"}}
    <input type="hidden" name="file_id" value="{{$file->_id}}"/>
  {{/if}}
  <input type="hidden" name="del" value="0"/>
</form>

<form method="post" name="moveFile_{{$file->_id}}" onsubmit="return onsubmitMoveFile(this)">
  <input type="hidden" name="dosql" value="do_move_file"/>
  <input type="hidden" name="m" value="dPfiles"/>
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="_guess_date" value="{{$guessing_date}}"/>

  <div id="main_move_file" style="position: relative;">
    <div style="position:absolute; left:0; width:50%; height:99%; text-align: center;">
      {{thumblink document=$file}}
        {{thumbnail document=$file profile=large style="border:solid 1px black; max-height: 800px; height:100%" alt=""}}
      {{/thumblink}}
    </div>

    <div id="object" style="position:absolute; left:50%; height:99%; overflow-y: auto;">
      <input type="hidden" name="file_id" value="{{$file->_id}}"/>
      <p>Renommer : <input type="text" name="file_name" value="{{$file->file_name}}" style="width:30em;"/></p>
      <p>Catégorie :
        <select name="category_id">
          <option value="">{{tr}}None{{/tr}}</option>
          {{foreach from=$file_categories item=_cat}}
            <option value="{{$_cat->_id}}" {{if $file->file_category_id == $_cat->_id}}selected="selected" {{/if}}>{{$_cat}}</option>
          {{/foreach}}
        </select>
      </p>
      {{if $file->_ref_object->_guid != $document->_guid}}
        <p><strong>Actuellement lié à :</strong> {{$file->_ref_object}}</p>
      {{/if}}
      <p><strong>Date du document : </strong>{{$document->document_date}}</p>
      {{if !$patient->_id}}<div class="small-info">Patient non retrouvé, faites une recherche manuelle ci dessous</div>{{/if}}
      <p><strong>Données patient : </strong>{{$document->patient_firstname}}, {{$document->patient_lastname}}, {{$document->patient_birthdate}}</p>
      <hr/>
      <input type="hidden" name="object_id" value=""/>
      <input type="hidden" name="object_class" value=""/>
      <div id="searchPat">
        {{mb_field object=$patient field="patient_id" hidden=1 ondblclick="PatSelector.init()" onchange="requestInfoPat();"}}
        <input type="text" name="_pat_name" style="width: 15em;" value="{{$patient}}" readonly="readonly" onclick="PatSelector.init()" />
        <button class="search notext" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
        <script>
          PatSelector.init = function() {
            this.sForm      = "moveFile_{{$file->_id}}";
            this.sId        = "patient_id";
            this.sView      = "_pat_name";
            var seekResult  = $V(getForm(this.sForm)._seek_patient).split(" ");
            this.sName      = seekResult[0] ? seekResult[0] : "{{$document->patient_lastname}}";
            this.sFirstName = seekResult[1] ? seekResult[1] : "{{$document->patient_firstname}}";
            this.pop();
          }
        </script>
        <input type="text" name="_seek_patient" style="width: 13em;" placeholder="{{tr}}fast-search{{/tr}}" class="autocomplete" />
        <div style="text-align: center">
          <button onclick="this.form.onsubmit()" type="button" class="tick" id="save_button" disabled="disabled">
            {{tr}}Lier{{/tr}}
          </button>
        </div>

      </div>
      <div id="resultSearch">

      </div>
    </div>
  </div>
</form>