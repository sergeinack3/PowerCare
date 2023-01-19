{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=antecedent}}
{{mb_default var=callback value=editAntecedent}}

<script>
  editAntecedent = function (antecedent_id) {
    var url = new Url("patients", "ajax_edit_antecedents");
    url.addParam("dialog", 1);
    url.addParam("patient_id", "{{$patient->_id}}");
    url.addParam("type", "{{$type}}");
    if (antecedent_id) {
      url.addParam("antecedent_id", antecedent_id);
    }
    url.addParam('callback', 'DossierMedical.reloadDossierPatient');
    url.requestUpdate('edit_antecedent_modal');
  };

  toggleChange = function (element) {
    var form = element.form;
    switch (element.name) {
      case "important" :
        form.__majeur.checked = false;
        $V(form.majeur, 0, false);
        break;
      case "majeur" :
        form.__important.checked = false;
        $V(form.important, 0, false);
    }
  };

  toggleOriginAutre = function (elt, form) {
    if ($V(elt) == "autre") {
      $("origin_autre_edit").show();
    } else {
      $("origin_autre_edit").hide();
      $V(form.origin_autre, '');
    }
  };

  submitAntecedent = function (form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        Control.Modal.close();
        {{$callback}}();
      }
    });
  }
</script>

<div id="edit_antecedent_modal">
  <form name="delAntecedent" method="post">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="dosql" value="do_antecedent_aed" />
    <input type="hidden" name="del" value="1" />
    <input type="hidden" name="antecedent_id" value="" />
    <input type="hidden" name="callback" value="editAntecedent" />
  </form>

  <form name="editAntFrm-{{$antecedent->_guid}}" method="post" onsubmit="return submitAntecedent(this)">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_antecedent_aed" />
    <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />

    {{mb_key object=$antecedent}}
    {{if $type}}
      <input type="hidden" name="type" value="{{$type}}" />
    {{/if}}

    <table class="form">
      <tr>
        <th class="title {{if $antecedent->_id}}modify{{else}}me-th-new{{/if}}" colspan="4">
          {{if $antecedent->_id}}
            <button type="button" class="new" onclick="editAntecedent()" style="float: left;">
              {{tr}}CAntecedent-action-New antecedent{{/tr}}
            </button>
          {{/if}}
          {{if $antecedent->_id}}
            {{tr}}CAntecedent-title-modify{{/tr}}
          {{else}}
            {{tr}}CAntecedent-title-create{{/tr}}
          {{/if}}
        </th>
      </tr>
      <tr>
        {{if $app->user_prefs.showDatesAntecedents}}
          <th style="height: 20px">{{mb_label object=$antecedent field=date}}</th>
          <td>{{mb_field object=$antecedent field=date form="editAntFrm-`$antecedent->_guid`" register=true}}</td>
        {{else}}
          <td colspan="2"></td>
        {{/if}}
        <td rowspan="{{if $antecedent->type === "alle"}}1{{else}}3{{/if}}" style="width: 60%">
	  Remarques :
	<br />
          {{mb_field object=$antecedent field="rques" rows="4" form="editAntFrm-`$antecedent->_guid`"
          aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
        </td>
	{{if $antecedent->type === "alle"}}
	   </tr>
	<tr>
	   <td colspan="2"></td>
	  <td rowspan="2">
	    R&eacute;action ind&eacute;sirable :
	   <br />
	    {{mb_field object=$antecedent field="reaction_indesirable" rows="4" form="editAntFrm-`$antecedent->_guid`"
          aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
	</td>
	{{/if}}
        {{if $type}}
          <td style="width: 40%; text-align: left; padding-left: 2em;" rowspan="{{$type|ternary:2:3}}" class="text">
            {{foreach from=$antecedents item=_antecedent}}
              <li {{if $_antecedent->annule}}class="cancelled" style="display: none;"{{/if}}>
                <div {{if $antecedent->_id == $_antecedent->_id}}class="selected"{{/if}}>
                  {{if $antecedent->owner_id == $app->user_id}}
                    <button title="{{tr}}Delete{{/tr}}" class="trash notext" type="button"
                            onclick="var form = getForm('delAntecedent'); $V(form.antecedent_id, '{{$_antecedent->_id}}');
                              Antecedent.remove(form)">
                      {{tr}}Delete{{/tr}}
                    </button>
                  {{/if}}
                  <strong>
                    {{if !$type}}
                      {{if $_antecedent->type    }} {{mb_value object=$_antecedent field=type    }} {{/if}}
                    {{/if}}
                    {{if $_antecedent->appareil}} {{mb_value object=$_antecedent field=appareil}} {{/if}}
                  </strong>
                  {{if $_antecedent->date}}
                    {{mb_value object=$_antecedent field=date}} :
                  {{/if}}
                  {{if $antecedent->owner_id == $app->user_id}}
                  <a href="#1" onclick="editAntecedent('{{$_antecedent->_id}}')">
                    {{/if}}
                    {{$_antecedent->rques|nl2br}}
                    {{if $antecedent->owner_id == $app->user_id}}
                  </a>
                  {{/if}}
                </div>
              </li>
              {{foreachelse}}
              <li class="empty">{{tr}}CAntecedent.unknown{{/tr}}</li>
            {{/foreach}}
          </td>
        {{/if}}
      </tr>

      {{if !$type}}
        <tr>
          <th style="height: 20px">{{mb_label object=$antecedent field="type"}}</th>
          <td>{{mb_field object=$antecedent field="type" emptyLabel="None" alphabet="1" style="width: 9em;" onchange="Antecedent.verifyType(this.form,'tr_family_link_modal')"}}</td>
        </tr>
        <tr id="tr_family_link_modal" {{if $antecedent->type != "fam"}}style="display: none"{{/if}}>
          <th>{{mb_label object=$antecedent field="family_link"}}</th>
          <td>{{mb_field object=$antecedent field="family_link" }}</td>
        </tr>
      {{/if}}
      <tr>
        <th style="height: 20px">{{mb_label object=$antecedent field="appareil"}}</th>
        <td>{{mb_field object=$antecedent field="appareil" emptyLabel="None" alphabet="1" style="width: 9em;"}}</td>
      </tr>
      <tr>
        <th title="{{tr}}CAntecedent-Level-desc{{/tr}}" style="height: 20px">{{tr}}CAntecedent-Level{{/tr}}</th>
        <td>{{mb_label object=$antecedent typeEnum="checkbox" field="important"}}
          {{mb_field object=$antecedent field="important" form="editAntFrm-`$antecedent->_guid`" typeEnum="checkbox" onchange="toggleChange(this)"}}</td>
        <td>{{mb_label object=$antecedent typeEnum="checkbox" field="majeur"}}
          {{mb_field object=$antecedent field="majeur" form="editAntFrm-`$antecedent->_guid`" typeEnum="checkbox" onchange="toggleChange(this)"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$antecedent field=origin}}</th>
        <td>
          {{mb_field object=$antecedent field=origin onchange="toggleOriginAutre(this, this.form);"}}
        </td>
        <td style="{{if $antecedent->origin != "autre"}}display: none;{{/if}}" id="origin_autre_edit">
          <input type="text" name="origin_autre" value="{{$antecedent->origin_autre}}" size="30" />
        </td>
      </tr>
      {{if "dPpatients CAntecedent display_antecedents_non_presents"|gconf}}
        <tr>
          <th></th>
          <td class="text" colspan="2">
            <label>
              {{mb_field object=$antecedent field=absence emptyLabel="None" typeEnum=checkbox}} ({{tr}}CAntecedent-absence-desc{{/tr}})
            </label>
          </td>
        </tr>
      {{elseif $antecedent->absence}}
        <tr>
          <th></th>
          <td colspan="2">{{tr}}CAntecedent-absence-desc{{/tr}}</td>
        </tr>
      {{/if}}
      <tr>
        <td class="button" colspan="4">
          {{if $antecedent->_id}}
            <button class="save" type="button" onclick="this.form.onsubmit();">
              {{tr}}Save{{/tr}}
            </button>
          {{else}}
            <button class="tick" type="button" onclick="this.form.onsubmit();">
              {{tr}}CAntecedent-action-Add the antecedent{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
</div>
