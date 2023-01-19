{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=category value=""}}

<style>
  #list_doc, #document_tab {
    height:730px;
    width: 48%;
    padding:5px;
    overflow-y:auto;
  }

  div.draggable{
    width:80px;
    height:80px;
    overflow: hidden;
    padding:5px;
    text-align: center;
    float:left;
  }

  #list_doc h3, #list_doc hr {
    clear:both;
  }

  #body_tab {
    position: relative;
  }

  .tab_dispo {
    height: 100%;
    width:100%;
    position:relative;
  }

  .tab_dispo .droppable {
    position: absolute;
    overflow: hidden;
    line-height: 100%;
    max-width: 100%;
    border: solid 1px #c6c6c6;
    text-align: center;
    vertical-align: middle!important;
    /*padding:5px;*/
  }

  .droppable img {
    max-width: 90%;
    max-height: 90%;
    vertical-align: middle;
  }

  .tab_dispo .droppable p {
    width:100%;
    margin:0 auto;
    position: absolute;
    text-align: center;
    bottom:0;
  }

  .tab_dispo .droppable input {
    width: 70%;
  }

  .tab_dispo .droppable:hover {
    border:dashed 1px black;
  }

  .nb_line_1 {height:100%;}
  .nb_line_2 {height:50%;}
  .nb_line_3 {height:33%;}

  .nb_col_1 {width:100%;}
  .nb_col_2 {width:50%;}
  .nb_col_3 {width:33%;}

  .line_1 {top:0;}
  .nb_line_2.line_2 {top:50%;}
  .nb_line_3.line_2 {top:33%;}
  .nb_line_3.line_3 {top:66%;}

  .col_1 {left:0;}
  .nb_col_2.col_2 {left:50%;}
  .nb_col_3.col_2 {left:33%;}
  .nb_col_3.col_3 {left:66%;}

</style>

<script>
  Main.add(function() {
    changeDisposition("tab_{{$default_disposition}}");

    $$(".droppable").each(function(li) {
      Droppables.add(li, {
        onDrop: function(from, to, event) {
          Event.stop(event);
          var img_from = $(from).down("img");
          var img_to = $(to).down("img");
          img_to.setAttribute("src", img_from.getAttribute("data-src"));
          var div_width = $(to).getStyle('width');
          var div_height = $(to).getStyle('height');
          img_to.setStyle({'max-width' : div_width, 'max-height' : div_height});

          var input_name = $(to).down("input.name");
          var input_img  = $(to).down("input.file_id");
          $V(input_name, $(from).getAttribute("data-name"));
          $V(input_img, $(from).getAttribute("data-file_id"));
          $(to).down("button").show();

        },
        accept: 'draggable',
        hoverclass:'dropover'
      });
    });

    $$(".draggable").each(function(a) {
      new Draggable(a, {
        onEnd: function(element, event) {
          Event.stop(event);
        },
        ghosting: true});
    });

  });

  removeImg = function(div_id) {
    $(div_id).down("img").setAttribute("src", "");
    $(div_id).select("input").each(function(elt) {
      $V(elt, '');
    });
    $(div_id).down("button").hide();
  };

  changeDisposition = function(dispo) {
    $$(".tab_dispo").each(function(elt) {
      elt.hide();
    });

    var oform = getForm("_document_to_create");
    $V(oform.tab_disposition, dispo, true);
    $(dispo).show();
  };

  printMoz = function() {
    var oform = getForm("_document_to_create");
    $V(oform.suppressHeaders, 1);
    $V(oform.print, 1);
    oform.submit();
  };
</script>

<div id="list_doc" style="float:left;">
  <h2 style="text-align: center;">{{$patient}}</h2>
  {{if $patient->_ref_files|@count}}
    <h3>{{tr}}{{$patient->_class}}{{/tr}}</h3>
    {{foreach from=$patient->_ref_files item=_file}}
      <div class="draggable"  data-file_id="{{$_file->_id}}" data-name="{{$_file->_no_extension}}">
        {{thumbnail document=$_file profile=medium style="max-width:80px; max-height: 60px;"
          data_src="?m=files&raw=thumbnail&document_guid=`$_file->_class`-`$_file->_id`&thumb=0" alt=""}}
        <br/>
        {{$_file}}
      </div>
    {{/foreach}}
  {{/if}}

  {{if $patient->_ref_consultations|@count}}
    {{foreach from=$patient->_ref_consultations item=_consult}}
      {{if $_consult->_ref_files|@count}}
        <h3 onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}');">{{tr}}{{$_consult->_class}}{{/tr}} {{mb_value object=$_consult->_ref_plageconsult field=date}}</h3>
        {{foreach from=$_consult->_ref_files item=_file}}
          {{if $_file->file_type}}
            <div class="draggable"  data-file_id="{{$_file->_id}}" data-name="{{$_file->_no_extension}}">
              {{thumbnail document=$_file profile=small
                data_src="?m=files&raw=thumbnail&document_guid=`$_file->_class`-`$_file->_id`&thumb=0" alt=""}}
              <br/>
              {{$_file}}
            </div>
          {{/if}}
        {{/foreach}}
      {{/if}}
    {{/foreach}}
  {{/if}}

  {{if $patient->_ref_sejours|@count}}
    {{foreach from=$patient->_ref_sejours item=_sejour}}
      {{if $_sejour->_ref_files|@count}}
        <h3>{{$_sejour}}</h3>
        {{foreach from=$_sejour->_ref_files item=_file}}
          {{if $_file->file_type}}
            <div class="draggable"  data-file_id="{{$_file->_id}}" data-name="{{$_file->_no_extension}}">
              {{thumbnail document=$_file profile=small
                data_src="?m=files&raw=thumbnail&document_guid=`$_file->_class`-`$_file->_id`&thumb=0" alt=""}}
              <br/>
              {{$_file}}
            </div>
          {{/if}}
        {{/foreach}}
      {{/if}}

      {{foreach from=$_sejour->_ref_operations item=_op}}
        {{if $_op->_ref_files|@count}}
          <h4>{{$_op}}</h4>
          {{foreach from=$_op->_ref_files item=_file}}
            <div class="draggable"  data-file_id="{{$_file->_id}}" data-name="{{$_file->_no_extension}}">
              {{thumbnail document=$_file profile=small
                data_src="?m=files&raw=thumbnail&document_guid=`$_file->_class`-`$_file->_id`&thumb=0" alt=""}}
              <br/>
              {{$_file}}
            </div>
          {{/foreach}}
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  {{/if}}

  {{if $patient->_ref_dossier_medical && $patient->_ref_dossier_medical->_ref_evenements_patient|@count}}
    {{foreach from=$patient->_ref_dossier_medical->_ref_evenements_patient item=_event}}
      {{if $_event->_ref_files|@count}}
        <h3 onmouseover="ObjectTooltip.createEx(this, '{{$_event->_guid}}');">{{tr}}{{$_event->_class}}{{/tr}} {{$_event}}</h3>
        {{foreach from=$_event->_ref_files item=_file}}
          {{if $_file->file_type}}
            <div class="draggable"  data-file_id="{{$_file->_id}}" data-name="{{$_file->_no_extension}}">
              {{thumbnail document=$_file profile=small
                data_src="?m=files&raw=thumbnail&document_guid=`$_file->_class`-`$_file->_id`&thumb=0" alt=""}}
              <br/>
              {{$_file}}
            </div>
          {{/if}}
        {{/foreach}}
      {{/if}}
    {{/foreach}}
  {{/if}}
</div>


<div id="document_tab" style="float:left;">
  <form name="_document_to_create" method="post" target="_blank"
        onsubmit="return onSubmitFormAjax(this, () => {
          if (window.refreshAfterAdd) {
            Control.Modal.close();
            window.refreshAfterAdd();
          }
          Control.Modal.close();
        });">
    <input type="hidden" name="m" value="dPfiles"/>
    <input type="hidden" name="dosql" value="do_mozaic_doc"/>
    <input type="hidden" name="suppressHeaders" value="0"/>
    <input type="hidden" name="print" value="0"/>
    <input type="hidden" name="context_guid" value="{{$context->_guid}}" />
    <label>
      {{tr}}CFile-Layout{{/tr}} :
      <select name="tab_disposition" onchange="changeDisposition($V(this));">
        {{foreach from=$matrices key=name item=_mat}}
          <option value="tab_{{$name}}">{{$name}}</option>
        {{/foreach}}
      </select>
    </label>|
    <label>
      {{tr}}CFilesCategory{{/tr}}
      <select name="category_id">
        <option value="" style="width: 15em;">{{tr}}CFilesCategory.none{{/tr}}</option>
        {{foreach from=$categories item=_category}}
          <option value="{{$_category->_id}}"
            {{if $category && $category === $_category->_id}}selected{{/if}}>{{$_category}}</option>
        {{/foreach}}
      </select>
    </label>
    <button class="save notext" title="Enregistrer et fermer">{{tr}}Save{{/tr}}</button>
    <button class="print notext" type="button" onclick="printMoz();">{{tr}}Print{{/tr}}</button>

    <div id="document_page" style="text-align:center; background-color:white; border:solid 1px #2b2b2b; box-shadow: 0 0 3px grey, 5px 5px 10px grey; margin:0 auto; height: 670px;">
      <table class="main" style="height:100%">
        <tr>
          <td id="header_tab" style="height: 10%;vertical-align: middle;">{{tr}}CFile-HEADER{{/tr}}</td>
        </tr>
        <tr>
          <td id="body_tab" style="height: 80%">
            {{foreach from=$matrices key=name item=_matrice}}
              {{assign var=lines value=$_matrice.line}}
              {{assign var=cols value=$_matrice.col}}
              <div id="tab_{{$name}}" class="tab_dispo">
                {{foreach from=1|range:$lines item=_line}}
                  {{foreach from=1|range:$cols item=_col}}
                    <div class="droppable col_{{$name}} nb_line_{{$lines}} nb_col_{{$cols}} col_{{$_col}} line_{{$_line}}" id="{{$name}}_{{$_line}}x{{$_col}}" data-file_id="">
                      <img src="" alt="">
                      <p>
                        <input type="hidden" class="file_id" name="file[tab_{{$name}}][{{$name}}_{{$_line}}x{{$_col}}][file_id]" value="" />
                        <input type="text" class="name" name="file[tab_{{$name}}][{{$name}}_{{$_line}}x{{$_col}}][name]">
                        <button type="button" class="trash notext" onclick="removeImg('{{$name}}_{{$_line}}x{{$_col}}')" style="display: none;" /></button>
                      </p>
                    </div>
                  {{/foreach}}
                {{/foreach}}
              </div>
            {{/foreach}}
          </td>
        </tr>
        <tr>
          <td id="footer_tab" style="height: 10%; vertical-align: middle;">{{tr}}CFile-FOOTER{{/tr}}</td>
        </tr>
      </table>
    </div>
  </form>
</div>
