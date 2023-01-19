{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_title value=1}}
{{mb_default var=show_create_button value=1}}

<script>
  updateScore = function() {
    var form = getForm('editChungScore');
    var total = 0;

    form.select('input[type=radio]:checked').each(function(input) {
      var value = parseInt($V(input));
      total += value;
      $('value_' + input.name).update(value);
    });

    $V(form.total, total + "");
    var display = $('display_total');

    /* Verification of the number of checked fields */
    var count_checked = 0;
    ['vital_signs', 'activity', 'nausea', 'pain', 'bleeding'].each(function(field) {
      if ($V(form[field]) != null) {
        count_checked = count_checked + 1;
      }
    });

    /* The total score is displayed only if all the fields are checked */
    if (count_checked == 5) {
      display.update(total);
      if (total >= 9) {
        display.removeClassName('error');
        display.addClassName('ok');
      }
      else {
        display.removeClassName('ok');
        display.addClassName('error');
      }
    }
    else {
      display.update("&nbsp;");
      display.removeClassName('ok');
      display.removeClassName('error');
    }
  };

  emptyField = function(field) {
    $A(getForm('editChungScore').elements[field]).each(function(input) {
      input.checked = false;
    });

    $('value_' + field).update('');
    updateScore();
  };
  
  chungOnComplete = function() {
    {{if $digest}}
      window.urlScoresDigest.refreshModal();
    {{else}}
      refreshFiches('{{$sejour->_id}}', 'chung_score');
    {{/if}} 
    
    Control.Modal.close(); 
    ExObject.checkNativeFieldInput('CSejour _latest_chung_score');
  };

  Main.add(function() {
    updateScore();
  });
</script>

<form name="editChungScore" action="?" method="post" onsubmit="return onSubmitFormAjax(this, chungOnComplete);">
  {{mb_class object=$chung_score}}
  {{mb_key object=$chung_score}}

  <input type="hidden" name="del" value="0"/>
  {{mb_field object=$chung_score field=sejour_id hidden=true}}
  {{mb_field object=$chung_score field=administration_id hidden=true}}

  <table class="tbl">
    {{if $show_title}}
    <tr>
      <th class="title{{if $chung_score->_id}} modifiy{{/if}}" colspan="6">
        {{if $chung_score->_id}}
          {{mb_include module=system template=inc_object_history object=$chung_score}}
          {{tr}}CChungScore-title-modify{{/tr}}
        {{else}}
          {{tr}}CChungScore-title-create{{/tr}}
        {{/if}}
      </th>
    </tr>
    {{/if}}
    <tr>
      <th class="category">{{tr}}Criteria{{/tr}}</th>
      <th class="category" colspan="3">{{tr}}Value{{/tr}}</th>
      <th class="category">Score</th>
      <th class="category narrow"></th>
    </tr>
    <tr>
      <th class="category narrow">{{mb_title object=$chung_score field=datetime}}</th>
      <td colspan="5">
        {{mb_field object=$chung_score field=datetime register=true form='editChungScore'}}
      </td>
    </tr>
    {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:criteria item=_criteria}}
      <tr>
        <th class="category">{{mb_title object=$chung_score field=$_criteria}}</th>
        <td>
          {{mb_field object=$chung_score field=$_criteria typeEnum=radio onclick='updateScore();' separator='</td><td>'}}
        </td>
        <td class="value" style="text-align: center;">
          <span id="value_{{$_criteria}}">{{if $chung_score->$_criteria}}{{mb_value object=$chung_score field=$_criteria}}{{/if}}</span>
        </td>
        <td>
          <button class="cancel notext" type="button" onclick="emptyField('{{$_criteria}}');">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <th class="category">
        {{mb_title object=$chung_score field=total}}
      </th>
      <td>
        {{mb_field object=$chung_score field=total hidden=true}}
        <span id="display_total" class="circled" style="font-weight: bold; text-align: center; font-size: 1.3em;">&nbsp;</span>
      </td>
      <td colspan="4" class="button">
        {{if $show_create_button}}
          {{if $chung_score->_id}}
            <button type="button" id="btn_edit_score" class="modify" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
            <button type="button" class="trash" onclick="confirmDeletion(this.form, { ajax:true, typeName:'{{tr}}CChungScore.this{{/tr}}'}, {onComplete: chungOnComplete})">
              {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button type="button" id="btn_edit_score" class="modify" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
          {{/if}}
        {{/if}}
      </td>
    </tr>
  </table>
</form>