{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$ged}}
  {{mb_script module=cim10 script=DRC ajax=true}}
{{/if}}

<style type="text/css">
  div.column {
    display: inline-block;
  }

  table.layout td {
    vertical-align: top;
  }

  ul.list {
    list-style-type: none;
    padding: 0px;
    border: 1px solid #bbb;
    overflow-y: auto;
  }

  table.list {
    border-right: 1px solid #bbb;
    border-top: 1px solid #bbb;
    border-spacing: 0;
  }

  table.list td {
    background-color: #fff;
    border-left: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
    padding: 5px;
  }

  table.list th {
    font-weight: bold;
    text-align: center;
    background-color: #bbb;
  }

  div#list_drc ul.list,
  div#list_siblings ul.list {
    height: 300px;
  }

  div#list_criteria ul.list {
    height: 630px;
  }

  div#follow_up_care ul.list {
    height: 120px;
  }

  div#follow_up_care {
    vertical-align: top;
    width: 225px;
  }

  div#diagnosis_position {
    width: 180px;
  }

  div#diagnosis_position ul.list {
    height: 120px;
  }

  div#list_diagnoses {
    border-top: 1px solid #bbb;
    height: 220px;
    overflow-y: auto;
  }

  div#list_transcodings ul.list {
    height: 220px;
    overflow-y: auto;
  }

  ul.list li {
    text-align: left;
    background-color: #fff;
    cursor: pointer;
    padding: 5px;
  }

  ul.list li:nth-of-type(odd),
  table.list tr:nth-of-type(odd) td
  {
    background-color: rgba(168, 183, 235, 0.10);
  }

  ul.list li:hover,
  table.list tr:hover td,
  table.list tr:nth-of-type(odd):hover td {
    background-color: rgba(168, 183, 235, 0.30);
  }

  ul.list li.selected,
  table.list tr.selected {
    background-color: rgba(165, 182, 235, 0.60);
  }

  ul li.selected:hover,
  table.list tr.selected:hover {
    background-color: rgba(167, 184, 235, 0.90);
  }

  #result_selected {
    border: 1px solid #999;
    background-color: rgb(138, 155, 205);
    padding: 2px;
    margin-left: 5px;
    margin-right: 5px;
  }
</style>

<table class="layout">
  <tr>
    <td colspan="3" id="search_drc" style="width: 100%; text-align: center;">
      {{mb_include module=cim10 template=drc/search}}
    </td>
  </tr>
  <tr>
    <td rowspan="3" style="width: 300px;">
      <div id="drc">
        <fieldset>
          <legend>{{tr}}CDRCConsultationResult-list{{/tr}}</legend>
          <div id="list_drc" style="width: 100%;">
            {{mb_include module=cim10 template=drc/list_drc}}
          </div>
        </fieldset>
      </div>
      <div id="siblings">
        <fieldset>
          <legend>{{tr}}CDRCConsultationResult-siblings{{/tr}}</legend>
          <div id="list_siblings" style="width: 100%;">
            {{mb_include module=cim10 template=drc/list_siblings}}
          </div>
        </fieldset>
      </div>
    </td>
    <td rowspan="3" style="width: 500px;">
      <div id="criteria">
        <fieldset>
          <legend style="vertical-align: middle;">
            {{tr}}CDRCConsultationResult-desc{{/tr}}
            <span id="result_selected" style="display: none;"></span>
            <button id="show_details" type="button" onclick="DRC.showResultDetails();" style="display: none;" title="{{tr}}CDRCConsultationResult-details{{/tr}}">
              <i class="fa fa-lg fa-list"></i>
            </button>
          </legend>
          <div id="list_criteria" style="height: 630px;">
            {{mb_include module=cim10 template=drc/list_criteria}}
          </div>
        </fieldset>
      </div>
    </td>
    <td style="height: 150px; width: 410px;">
      <div id="diagnosis_position" style="display: inline-block;">
        <fieldset>
          <legend>{{tr}}CDRCConsultationResult-diagnosis_position{{/tr}}</legend>
          <div id="list_positions" style="width: 100%; height: 120px;">
            {{mb_include module=cim10 template=drc/list_positions}}
          </div>
        </fieldset>
      </div>
      <div id="follow_up_care" style="display: inline-block;">
        <fieldset>
          <legend>{{tr}}CDRCConsultationResult-follow_up{{/tr}}</legend>
          <div id="follow_up" style="width: 100%; height: 120px; vertical-align: top;">
            {{mb_include module=cim10 template=drc/follow_up}}
          </div>
        </fieldset>
      </div>
    </td>
  </tr>
  <tr>
    <td style="height: 250px; width: 410px;">
      <div id="criticial_diagnoses">
        <fieldset>
          <legend>{{tr}}CDRCConsultationResult-critical_diagnoses{{/tr}}</legend>
          <div id="list_diagnoses">
            {{mb_include module=cim10 template=drc/critical_diagnoses}}
          </div>
        </fieldset>
      </div>
    </td>
  </tr>
  <tr>
    <td style="height: 250px; width: 410px;">
      <div id="transcodings">
        <fieldset>
          <legend>{{tr}}CDRCConsultationResult-transcodings{{/tr}}</legend>
          <div id="list_transcodings">
            {{mb_include module=cim10 template=drc/transcodings}}
          </div>
        </fieldset>
      </div>
    </td>
  </tr>
  <tr>
    <td id="result_actions" colspan="3" style="text-align: center; display: none;">
      {{if $consult->_id || $mode == 'antecedents'}}
        <button type="button" class="save" onclick="DRC.setCodeCIM(null, '{{$mode}}');Control.Modal.close();">
          Enregistrer le diagnostic CIM10
        </button>
      {{/if}}
      {{if $ged}}
        <button type="button" class="tick me-primary" title="{{tr}}CCompteRendu-benchmark select CIM10 match{{/tr}}"
                onclick="DRC.selectCode()">
          {{tr}}CCompteRendu-benchmark select CIM10 match{{/tr}}
        </button>
      {{/if}}
      <button type="button" class="fa fa-copy" onclick="DRC.displayCopyResult();">
        Copier le résultat
      </button>
    </td>
  </tr>
</table>

<div id="copy_result" style="display: none;">
  <form name="copy_result_text" method="post" onsubmit="return onSubmitFormAjax(this);">
    <div style="text-align: center;">
      <textarea name="_result_text" cols="100" rows="15"></textarea>

      {{if $consult->_id}}
        {{mb_class object=$consult}}
        {{mb_key object=$consult}}

        {{mb_field object=$consult field=motif hidden=true}}
        {{mb_field object=$consult field=histoire_maladie hidden=true}}
        {{mb_field object=$consult field=conclusion hidden=true}}

        <button type="button" class="fa fa-copy" onclick="DRC.copyToField('motif');" title="Copier dans le champ Motif">Motif</button>
        <button type="button" class="fa fa-copy" onclick="DRC.copyToField('histoire_maladie');" title="Copier dans le champ Histoire de la maladie">Histoire de la maladie</button>
        <button type="button" class="fa fa-copy" onclick="DRC.copyToField('conclusion');" title="Copier dans le champ Au total">Au total</button>
      {{/if}}

      <button id="btn_clipboard" type="button" class="fa fa-clipboard" title="Copier dans le presse-papier">Presse-papier</button>
    </div>
  </form>


</div>

<div id="cim_code_selection" style="display: none;">
  <form name="CodeCIMSelection" method="post" onsubmit="return false;">
    <div style="text-align: center; width: 400px;">
      <ul class="list" id="list_codes_cim"></ul>
      <button id="btn_select_code" type="button" class="tick">{{tr}}Validate{{/tr}}</button>
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </div>
  </form>
</div>

<form name="editDossier" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key object=$dossier}}

  {{if !$dossier->_id}}
    <input type="hidden" name="object_id" value="{{$patient->_id}}">
    <input type="hidden" name="object_class" value="CPatient">
  {{/if}}

  {{mb_field object=$dossier field=codes_cim hidden=true}}
</form>

<div id="result_details" style="display: none;">

</div>
