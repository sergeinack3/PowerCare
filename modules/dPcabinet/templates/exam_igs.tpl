{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

checkSelect = function(){
  var oForm = getForm("editScoreIGS");
  var score_igs = 0;
  
  oForm.select("input[type=radio]:checked:not(.empty_value)").each(function(oRadio){
    var radio_value = $V(oRadio);
    var checked_value = parseInt(radio_value,10);
    score_igs += checked_value;

    var value_content = checked_value;

    if (checked_value === 0) {
      value_content = DOM.strong({}, 0);
    }

    oRadio.up('tr').down('.value').update(value_content);
  });
  
  $V(oForm.scoreIGS, score_igs + "");
  var simplified_igs = score_igs - parseInt($V(oForm.age), 10);
  $V(oForm.simplified_igs, simplified_igs + "");
};

empty_on_click = function(elem) {
  $A(getForm("editScoreIGS").elements[elem]).each(function(radio){
    radio.checked = false;
  });
  
  $("editScoreIGS_"+elem+"_").checked = true;
  
  getForm("editScoreIGS").elements[elem][0].up('tr').down('.value').update('');
  checkSelect();     
};

showLaboResult = function() {
  var url = new Url("dPImeds", "httpreq_vw_sejour_results");
  url.addParam("sejour_id", "{{$sejour->_id}}");
  url.popup(800, 700);
};

Main.add(checkSelect);

</script>

<form name="editScoreIGS" method="post" action="?" onsubmit="return onSubmitFormAjax(this, { onComplete: function(){ {{if $digest}}window.urlScoresDigest.refreshModal();{{else}}refreshFiches('{{$sejour->_id}}');{{/if}} Control.Modal.close(); } });">
  {{mb_key   object=$exam_igs}}
  {{mb_class object=$exam_igs}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <table class="tbl">
    <tr>
      <th class="title {{if $exam_igs->_id}}modify{{else}}me-th-new{{/if}}" colspan="10">
        <button type="button" style="float: right" onclick="showLaboResult();" class="search">Labo</button>
        {{if $exam_igs->_id}}
          {{mb_include module=system template=inc_object_history object=$exam_igs}}
          {{tr}}{{$exam_igs->_class}}-title-modify{{/tr}} 
          <br />
          '{{$exam_igs}}'
        {{else}}
          {{tr}}{{$exam_igs->_class}}-title-create{{/tr}} 
        {{/if}}
    </tr>
    <tr>
      <th class="category">Paramètre</th>
      <th class="category" colspan="6">Sélection</th>
      <th class="category">Valeur</th>
      {{if !$exam_igs->_id}}
      <th class="category">Dernière<br />constante</th>
      {{/if}}
      <th class="category"></th>
    </tr>
    <tr>
      <th>{{tr}}Date{{/tr}}</th>
      <td colspan="9">
        {{if $exam_igs->_id}}
          {{mb_value object=$exam_igs field=date}}
        {{else}}
          {{mb_field object=$exam_igs field=date form=editScoreIGS register=true onchange="Control.Modal.close(); openScoreIGS('0', this.value);"}}
        {{/if}}
      </td>
    </tr>
    {{foreach from='Ox\Mediboard\Cabinet\CExamIgs'|static:fields item=_field}}
    <tr>
      <th>
        {{mb_label object=$exam_igs field=$_field}}
      </th>
      <td style="width: 80px;">
        {{mb_field object=$exam_igs field=$_field typeEnum="radio" onclick="checkSelect();" separator="</td><td style='width: 80px;'>"}}
         <input type="radio" value="" name="{{$_field}}" id="editScoreIGS_{{$_field}}_" class="empty_value" style="display: none;">
      </td>
      <!-- Calcul du nombre de td à rajouter pour compléter la ligne -->
      {{math equation=6-x x=$exam_igs->_specs.$_field->_list|@count assign=nb_colonne}}
      {{if $nb_colonne}}
        <td colspan="{{$nb_colonne}}"></td>
      {{/if}}
      <td class="value" style="text-align: center;"></td>
      {{if !$exam_igs->_id}}
      <td style="text-align: center;">
        {{if array_key_exists($_field, $last_constantes)}}
          {{$last_constantes.$_field}}
        {{/if}}
      </td>  
      {{/if}}
      <td>
        <button type='button' class='cancel notext' onclick="empty_on_click('{{$_field}}')"></button>
      </td>
    </tr>
    {{/foreach}}
    <tr>
      <th class="title">
        {{mb_label object=$exam_igs field="scoreIGS"}}
      </th>
      <td>
        {{mb_field object=$exam_igs field="scoreIGS" readonly="readonly" style="font-weight: bold; text-align: center; font-size: 1.2em;"}}
      </td>
      <th class="category">
        {{mb_label object=$exam_igs field=simplified_igs}}
      </th>
      <td>
        {{mb_field object=$exam_igs field=simplified_igs readonly="readonly" style="text-align: center; font-size: 1.1em;"}}
      </td>
      <td colspan="6">
        {{if $exam_igs->_id}}
          <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash" onclick="confirmDeletion(this.form, { ajax:true, typeName:'cet examen IGS'}, {onComplete: function(){ {{if $digest}}window.urlScoresDigest.refreshModal();{{else}}refreshFiches('{{$sejour->_id}}');{{/if}} Control.Modal.close(); } })">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button type="submit" class="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>