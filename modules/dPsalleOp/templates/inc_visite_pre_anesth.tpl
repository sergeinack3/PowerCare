{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback value=''}}

<script>
  refreshAidesPreAnesth = function(user_id, user_view) {
    aideRquesAnesth.options.contextUserId = user_id;
    aideRquesAnesth.options.contextUserView = user_view;
    aideRquesAnesth.init();
  };

  submitVPA = function(form, sel_guid, see) {
    onSubmitFormAjax(form,
      function(){
        var vpa_operation = $('vpa_'+sel_guid);
        if (vpa_operation) {
          if (see == 1) {
            vpa_operation.show();
          }
          else {
            vpa_operation.hide();
          }
        }
      });
  };

  Main.add(function() {
    var oFormVisiteAnesth = getForm("visiteAnesth");
    var contextUserId   = User.id;
    var contextUserView = User.view;

    {{if $selOp->prat_visite_anesth_id}}
      contextUserId = {{$selOp->prat_visite_anesth_id}};

      {{if $selOp->date_visite_anesth || $app->_ref_user->isAnesth()}}
        contextUserView = "{{$selOp->_ref_anesth_visite->_view|smarty:nodefaults:JSAttribute}}";
      {{else}}
        var oForm = getForm('visiteAnesth');
        contextUserView = oForm.prat_visite_anesth_id.options[oForm.prat_visite_anesth_id.selectedIndex].innerHTML.trim();
      {{/if}}
    {{/if}}

    aideRquesAnesth = new AideSaisie.AutoComplete(oFormVisiteAnesth.rques_visite_anesth, {
      objectClass: "COperation",
      timestamp: "{{$conf.dPcompteRendu.CCompteRendu.timestamp}}",
      {{if $selOp->prat_visite_anesth_id}}
        contextUserId: contextUserId,
        contextUserView: contextUserView,
      {{/if}}
      validateOnBlur:0
    });

    {{if !$selOp->date_visite_anesth}}
      Calendar.regField(oFormVisiteAnesth.date_visite_anesth);

      // Initialisation du champ date
      $("visiteAnesth_date_visite_anesth_da").value = "Date actuelle";
      $V(oFormVisiteAnesth.date_visite_anesth, "current");

      {{if "dPsalleOp COperation use_time_vpa"|gconf}}
        Calendar.regField(oFormVisiteAnesth.time_visite_anesth);
        $("visiteAnesth_time_visite_anesth_da").value = "Heure actuelle";
        $V(oFormVisiteAnesth.time_visite_anesth, "now");
      {{/if}}
    {{/if}}

    if ($('anesth_tab_group')){
      {{if $selOp->date_visite_anesth}}
      $('anesth_tab_group').select('a[href=#tab_preanesth]')[0].removeClassName('wrong');
      {{else}}
      $('anesth_tab_group').select('a[href=#tab_preanesth]')[0].addClassName('wrong');
      {{/if}}
    }
  });
</script>

<form name="visiteAnesth" action="?m={{$m}}" method="post" onsubmit="{{if $callback}}return false;{{elseif $onSubmit}}{{$onSubmit}}{{/if}}">
  <input type="hidden" name="dosql" value="do_planning_aed" />
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$selOp}}
  {{if $selOp->date_visite_anesth}}
    <input name="prat_visite_anesth_id" type="hidden" value="{{$selOp->prat_visite_anesth_id}}" />
    <input name="date_visite_anesth"    type="hidden" value="" />
    <input name="time_visite_anesth"    type="hidden" value="" />
    <input name="rques_visite_anesth"   type="hidden" value="" />
  {{/if}}
  {{if $callback}}
    <input type="hidden" name="ajax" value="1" />
    <input type="hidden" name="callback" value="{{$callback}}" />
  {{/if}}
<table class="form">
  <tr>
    <th class="title" colspan="2">Visite préanesthésique</th>
  </tr>
  <tr>
    <th>{{mb_label object=$selOp field="date_visite_anesth"}}</th>
    <td>
      {{if $selOp->date_visite_anesth}}
        {{mb_value object=$selOp field="date_visite_anesth"}}
        {{if "dPsalleOp COperation use_time_vpa"|gconf && $selOp->time_visite_anesth}}
          {{mb_value object=$selOp field="time_visite_anesth"}}
        {{/if}}
      {{else}}
        {{mb_field object=$selOp field="date_visite_anesth"}}
        {{if "dPsalleOp COperation use_time_vpa"|gconf}}
          {{mb_field object=$selOp field="time_visite_anesth"}}
        {{/if}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$selOp field="prat_visite_anesth_id"}}</th>
    <td>
      {{if $selOp->date_visite_anesth}}
      Dr {{mb_value object=$selOp field="prat_visite_anesth_id"}}
      {{elseif $app->_ref_user->isAnesth()}}
      <input name="prat_visite_anesth_id" type="hidden" value="{{$app->_ref_user->_id}}" />
      Dr {{$app->_ref_user->_view}}
      {{else}}
      <select name="prat_visite_anesth_id" class="notNull" onchange="refreshAidesPreAnesth($V(this), this.options[this.selectedIndex].innerHTML.trim())">
        <option value="">&mdash; Anesthésiste</option>
        {{mb_include module=mediusers template=inc_options_mediuser list=$listAnesths selected=$selOp->prat_visite_anesth_id}}
      </select>
    {{/if}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$selOp field="rques_visite_anesth"}}
    </th>
    <td class="text">
      {{if $selOp->date_visite_anesth}}
        {{mb_value object=$selOp field="rques_visite_anesth"}}
      {{else}}
        {{mb_field object=$selOp field="rques_visite_anesth" class="notNull"}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$selOp field="autorisation_anesth"}}</th>
    <td>
      {{if $selOp->date_visite_anesth}}
        {{mb_value object=$selOp field="autorisation_anesth"}}
      {{else}}
        {{mb_field object=$selOp field="autorisation_anesth"}}
      {{/if}}
    </td>
  </tr>
  {{if !$selOp->date_visite_anesth && !$app->_ref_user->isAnesth()}}
  <tr>
    <th>{{mb_label object=$selOp field="_password_visite_anesth"}}</th>
    <td>{{mb_field object=$selOp field="_password_visite_anesth"}}</td>
  </tr>
  {{/if}}
  {{if !$selOp->date_visite_anesth}}
    <tr>
      <td class="button" colspan="2">
        <button class="submit" {{if $callback}}type="button" onclick="submitVPA(this.form, '{{$selOp->_guid}}', 0);"{{/if}}>
          {{tr}}Validate{{/tr}}
        </button>
      </td>
    </tr>
  {{else}}
    {{if $app->_ref_user->_id != $selOp->prat_visite_anesth_id}}
      <tr>
        <th>{{mb_label object=$selOp field="_password_visite_anesth"}}</th>
        <td>{{mb_field object=$selOp field="_password_visite_anesth"}}</td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        <button class="trash" {{if $callback}}type="button" onclick="submitVPA(this.form, '{{$selOp->_guid}}', 1);"{{/if}}>
          {{tr}}Cancel{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}
</table>
</form>