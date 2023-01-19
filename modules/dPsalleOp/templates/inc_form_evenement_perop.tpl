{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=incident value=0}}
<script>
  submitPerOp = function(form){
    {{if $incident == 1}}
      if ($V(form.antecedent)) {
        saveIncident(form);
      }
    {{/if}}
    return onSubmitFormAjax(form, {
      onComplete: function(){
        refreshAnesthPerops('{{$selOp->_id}}');
        $V(form.libelle, '');
      }.bind(this)  });
  }
  saveIncident = function(formAnt){
    var form = getForm('addAntecedentIncident');
    if ($V(formAnt.codecim)) {
      $V(form.rques, formAnt.libelle.value+' '+$V(formAnt.codecim));
    }
    else {
      $V(form.rques, formAnt.libelle.value);
    }
    return onSubmitFormAjax(form, {
      onComplete: function(){
        $V(formAnt.codecim, '');
        $V(formAnt.antecedent, 0);
      }});
  }

  showCIMs10 = function(form){
    var url = new Url("cim10", "find_codes_antecedent");
    url.addParam("mater", '{{$selOp->_ref_sejour->grossesse_id}}');
    url.requestUpdate('print_fiche_area');
  }

  incidentAntecedent = function(form){
    if ($V(form.antecedent)) {
      $('print_fiche_area').show();
    }
    else {
      $('print_fiche_area').hide();
    }
  }

  {{if $incident == 1}}
  Main.add(function () {
    showCIMs10();
    incidentAntecedent(getForm('addAnesthPerop-{{$incident}}'));
  });
  {{/if}}
</script>

<form name="addAnesthPerop-{{$incident}}" action="?" method="post">
  <input type="hidden" name="m" value="dPsalleOp" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_anesth_perop_aed" />
  <input type="hidden" name="operation_id" value="{{$selOp->_id}}" />
  <input type="hidden" name="datetime" value="now" />
  <input type="hidden" name="anesth_perop_id" />
  {{mb_class class=CAnesthPerop}}

  {{if $incident == 1}}
    <input type="hidden" name="incident" value="1" />
  {{/if}}
  <table class="main layout">
    <tr>
      <td>
        {{if $selOp->_ref_anesth->_id}}
          {{assign var=contextUserId value=$selOp->_ref_anesth->_id}}
          {{assign var=contextUserView value=$selOp->_ref_anesth->_view|smarty:nodefaults:JSAttribute}}
        {{else}}
          {{assign var=contextUserId value=$app->_ref_user->_id}}
          {{assign var=contextUserView value=$app->_ref_user->_view|smarty:nodefaults:JSAttribute}}
        {{/if}}
        {{mb_field class=CAnesthPerop field="libelle" form="addAnesthPerop-$incident"
        aidesaisie="contextUserId: '$contextUserId', contextUserView: '$contextUserView'"}}
      </td>
    </tr>
    {{if $incident == 1}}
      <tr>
        <td>
          <input name="antecedent" type="checkbox" value="" onchange="incidentAntecedent(this.form);"/>
          {{tr}}CAntecedent-action-Make an antecedent{{/tr}}
        </td>
      </tr>
      <tr id="print_fiche_area"></tr>
    {{/if}}
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="submit" onclick="return submitPerOp(this.form);">{{tr}}common-action-Add{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if $incident == 1}}
  <form name="addAntecedentIncident" action="?" method="post">
    <input type="hidden" name="m"           value="patients" />
    <input type="hidden" name="del"         value="0" />
    <input type="hidden" name="dosql"       value="do_antecedent_aed" />
    <input type="hidden" name="_patient_id" value="{{$selOp->_ref_sejour->patient_id}}" />
    <input type="hidden" name="_sejour_id"  value="{{$selOp->sejour_id}}" />
    <input type="hidden" name="date"        value="now" />
    <input type="hidden" name="type"        value="anesth" />
    <input type="hidden" name="rques"       value="" />
  </form>
{{/if}}