{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=last_item    value=false}}
{{mb_default var=list_chirs   value=null}}
{{mb_default var=list_anesths value=null}}
{{mb_default var=preview      value=false}}
{{mb_default var=multi_ouverture value=false}}
{{mb_default var=choose_moment_edit value="dPsalleOp CDailyCheckList choose_moment_edit"|gconf}}
{{mb_default var=name_form_checklist value="edit-CDailyCheckList-`$check_list->object_class`-`$check_list->object_id`-`$check_list->type`-`$check_list->list_type_id`"}}

{{mb_script module=salleOp script=edit_checklist ajax=1}}
<script>
  Main.add(function(){
    EditDailyCheck.HAS_classes = {{'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes|@json}};
    EditDailyCheck.preview = '{{$preview}}';
    EditDailyCheck.dToday = '{{$dnow}}';
    {{if $check_list->code_red}}
      EditDailyCheck.checkedCodeRouge();
    {{/if}}
  });
</script>

{{if $check_list->isReadonly()}}
  <table class="main tbl">
    {{assign var=category_id value=0}}
    {{foreach from=$check_list->_ref_item_types item=curr_type name="loop_items_types"}}
      {{assign var=curr_cat value=$curr_type->category_id}}
      {{if array_key_exists($curr_cat, $check_item_categories)}}
        {{if $curr_type->category_id != $category_id}}
          <tr>
            <th colspan="2" class="text category" style="text-align: left;">
              <strong>{{$check_item_categories.$curr_cat->title}}</strong>
              {{if $check_item_categories.$curr_cat->desc}}
                &ndash; {{$check_item_categories.$curr_cat->desc}}
              {{/if}}
            </th>
          </tr>
        {{/if}}
        {{assign var=red_code value='Ox\Mediboard\SalleOp\CDailyCheckList::itemCanRedCode'|static_call:$check_list->object_class:$check_list->type:$smarty.foreach.loop_items_types.index}}
        <tr {{if $red_code}}style="background-color: rgba(255,0,0,0.2);"{{/if}}>
          <td style="padding-left: 1em;" class="text">
            {{mb_value object=$curr_type field=title}}
            {{if $curr_type->desc}}
              <div class="compact" style="margin-top: 0.5em;">{{mb_value object=$curr_type field=desc}}</div>
            {{/if}}
          </td>
          <td class="text">
            {{$curr_type->_answer}}
            {{if $curr_type->_commentaire}}
              (<span title="{{$curr_type->_commentaire}}">{{$curr_type->_commentaire|truncate:25:"...":true}}</span>)
            {{/if}}
          </td>
        </tr>
      {{/if}}
      {{assign var=category_id value=$curr_type->category_id}}
    {{foreachelse}}
      <tr>
        <td colspan="2" class="empty">{{tr}}CDailyCheckItemType.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{if !in_array($check_list->object_class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes) || $last_item
        || in_array($check_list->type, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_last_types)}}
      <tr>
        <td colspan="2">
          <strong>Commentaires:</strong><br />
          {{mb_value object=$check_list field=comments}}
        </td>
      </tr>
    {{/if}}
    {{if $check_list->type == "preop_2016" || $check_list->decision_go}}
      <tr>
        <td colspan="2" class="text">
          <hr />
          <strong>{{mb_label object=$check_list field=decision_go}}</strong>:
          <span style="font-size: 1.2em;
            {{if $check_list->decision_go == "nogo"}}color:red;{{elseif $check_list->decision_go == "go"}}color:green;{{/if}}">
            {{mb_value object=$check_list field=decision_go}}
            {{if $check_list->decision_go == "nogo"}}
              ({{mb_value object=$check_list field=result_nogo}})
            {{/if}}
          </span>
        </td>
      </tr>
    {{/if}}

    <tr>
      <td colspan="2" class="button">
        <strong>
          {{tr}}CDailyCheckList-Validated by{{/tr}} {{mb_value object=$check_list field=validator_id}}
          {{if $check_list->date_validate}}
            {{tr var1=$check_list->date_validate|date_format:$conf.date var2=$check_list->date_validate|date_format:$conf.time}}
              common-the %s at %s
            {{/tr}}
          {{/if}}
        </strong>

        {{if 'Ox\Core\CMbDT::minutesRelative'|static_call:$check_list->date_validate:$dtnow < 1440
              && ($app->_ref_user->_id == $check_list->validator_id )}}
          <br/>
          <form name="unvalidate-{{$check_list->_guid}}" method="post" onsubmit="onSubmitFormAjax(this, function() {
            EditDailyCheck.refreshCheckListValidate('{{$check_list->type}}', '{{$check_list->list_type_id}}', '{{$check_list->_id}}');
            });">
            {{mb_key object=$check_list}}
            {{mb_class object=$check_list}}
            <input type="hidden" name="validator_id" value=""/>
            <input type="hidden" name="date_validate" value=""/>
            <button type="button" class="cancel" onclick="this.form.onsubmit();" title="{{tr}}CDailyCheckList-invalide24hours{{/tr}}">
              {{tr}}Cancel-validation{{/tr}}
            </button>
          </form>
        {{/if}}
      </td>
    </tr>
    {{if $check_list->_ref_list_type->_id && $check_list->_ref_list_type->use_validate_2}}
      <tr>
        <td colspan="2">
          {{mb_label object=$check_list field=com_validate2}}
          {{if $check_list->date_validate2}}
            {{mb_value object=$check_list field=com_validate2}}
          {{else}}
            <form name="com_validate2-{{$check_list->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
              {{mb_key object=$check_list}}
              {{mb_class object=$check_list}}
              {{mb_field object=$check_list field=com_validate2 onchange="this.form.onsubmit();"}}
            </form>
          {{/if}}
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
          {{if $check_list->date_validate2}}
            <strong>
              Contre-Validé par {{mb_value object=$check_list field=validator2_id}}
              {{if $check_list->date_validate2}}
                {{tr var1=$check_list->date_validate2|date_format:$conf.date var2=$check_list->date_validate2|date_format:$conf.time}}
                  common-the %s at %s
                {{/tr}}
              {{/if}}
            </strong>
            {{if 'Ox\Core\CMbDT::minutesRelative'|static_call:$check_list->date_validate2:$dtnow < 1440
            && ($app->_ref_user->_id == $check_list->validator2_id || $app->_ref_user->isAdmin())}}
              <br/>
              <form name="unvalidate2-{{$check_list->_guid}}" method="post" onsubmit="onSubmitFormAjax(this, function() {
                EditDailyCheck.refreshCheckListValidate('{{$check_list->type}}', '{{$check_list->list_type_id}}', '{{$check_list->_id}}');
                });">
                {{mb_key object=$check_list}}
                {{mb_class object=$check_list}}
                <input type="hidden" name="validator2_id" value=""/>
                <input type="hidden" name="date_validate2" value=""/>
                <button type="button" class="cancel" onclick="this.form.onsubmit();" title="{{tr}}CDailyCheckList-invalide24hours{{/tr}}">
                  {{tr}}Cancel-validation2{{/tr}}
                </button>
              </form>
            {{/if}}
          {{else}}
            <form name="validation2-{{$check_list->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this, function() {
              if (EditDailyCheck.checkReloadLocation(this)) {
                location.reload();
              }
              else {
                EditDailyCheck.refreshCheckListValidate('{{$check_list->type}}', '{{$check_list->list_type_id}}', '{{$check_list->_id}}');
              }
              });">
              {{mb_key object=$check_list}}
              {{mb_class object=$check_list}}
              <input type="hidden" name="ref_type_list" value="{{$check_list->_ref_list_type->type}}" />
              <input type="hidden" name="multi_ouverture" value="{{$multi_ouverture}}" />
              <input type="hidden" name="choose_moment_edit" value="{{$choose_moment_edit}}" />
              <input type="hidden" name="object_class" value="{{$check_list->object_class}}" />
              <label for="validator2_id" style="display: none;">{{tr}}CDailyCheckList-validator2_id{{/tr}}</label>
              <select name="validator2_id" class="notNull ref" style="width: 10em;">
                <option value="" disabled="disabled" selected="selected">&mdash; Validateur</option>
                {{assign var=type_validateur value='|'|explode:$check_list->_ref_list_type->type_validateur}}
                {{if !($check_list->object_class == "COperation" && !$check_list->list_type_id) && $list_anesths}}
                  <optgroup label="Anesthésistes">
                    {{mb_include module=mediusers template=inc_options_mediuser list=$list_anesths selected=$app->user_id}}
                  </optgroup>
                {{/if}}
              </select>
              <label for="_validator_password" style="display: none;">{{tr}}CDailyCheckList-_validator_password{{/tr}}</label>
              <input type="password" class="notNull str" size="10" maxlength="32" name="_validator_password"/>
              <button type="button" class="tick" onclick="this.form.onsubmit();">Contre-Signer</button>
            </form>
          {{/if}}
        </td>
      </tr>
    {{/if}}
    {{if $check_list->type == "postop_2016"}}
      {{mb_include module=salleOp template=alert_child_cheklist}}
    {{/if}}
  </table>
  {{mb_return}}
{{/if}}

<script>
refreshCheckList{{$check_list->type}}_{{$check_list->list_type_id}} = function(id){
  var form = getForm("{{$name_form_checklist}}");
  if ($V(form.validator_id) && $V(form._signature) == 1 && !$("systemMsg").select(".warning, .error").length) {
    if ($('{{$check_list->type}}'+'-title')) {
      if ($('{{$check_list->type}}'+'-title').down("i")) {
        $('{{$check_list->type}}'+'-title').down("i").removeClassName('me-error');
        $('{{$check_list->type}}'+'-title').down("i").addClassName('tick me-success');
      }
    }
    else if ($('{{$check_list->list_type_id}}'+'-param-title')) {
      $('{{$check_list->list_type_id}}'+'-param-title').down("i").removeClassName('me-error');
      $('{{$check_list->list_type_id}}'+'-param-title').down("i").addClassName('tick me-success');
    }
    EditDailyCheck.refreshCheckListValidate('{{$check_list->type}}', '{{$check_list->list_type_id}}', id);
  }
  else {
    if (!$V(form.daily_check_list_id)) {
      $V(form.daily_check_list_id, id);
    }
  }
};

saveCheckListIdCallback{{$check_list->type}}_{{$check_list->list_type_id}} = function(id) {
  var form = getForm("{{$name_form_checklist}}");

  if (!$V(form.daily_check_list_id)) {
    $V(form.daily_check_list_id, id);
  }
};

Main.add(function(){
  prepareForm('{{$name_form_checklist}}');
  EditDailyCheck.changeValidator($('{{$name_form_checklist}}_validator_id'), {{$app->user_id}});
  {{if $check_list->code_red}}
    EditDailyCheck.checkedCodeRouge();
  {{/if}}
});
</script>

<form name="{{$name_form_checklist}}"
      method="post" action="?" onsubmit="{{if $preview}} return false; {{else}} return EditDailyCheck.submitCheckList(this, false); {{/if}}">
  <input type="hidden" name="dosql" value="do_daily_check_list_aed" />
  <input type="hidden" name="m" value="salleOp" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="daily_check_list_id" value="{{$check_list->_id}}" />
  <input type="hidden" name="object_class" value="{{$check_list->object_class}}" />
  <input type="hidden" name="object_id" value="{{$check_list->object_id}}" />
  <input type="hidden" name="type" value="{{$check_list->type}}" />
  <input type="hidden" name="list_type_id" value="{{$check_list->list_type_id}}" />
  <input type="hidden" name="ref_type_list" value="{{$check_list->_ref_list_type->type}}" />
  <input type="hidden" name="multi_ouverture" value="{{$multi_ouverture}}" />
  <input type="hidden" name="choose_moment_edit" value="{{$choose_moment_edit}}" />
  <input type="hidden" name="date" value="{{$check_list->date|ternary:$check_list->date:"now"}}" />
  <input type="hidden" name="group_id" value="{{$check_list->group_id|ternary:$check_list->group_id:$g}}" />
  <input type="hidden" name="code_red" value="{{$check_list->code_red}}" />
  <input type="hidden" name="_signature" value="0" />

  {{if in_array($check_list->object_class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes) ||
  ($multi_ouverture || $check_list->_ref_list_type->type == "fermeture_salle" || $check_list->_ref_list_type->type == "fermeture_sspi" || $check_list->_ref_list_type->type == "fermeture_preop" ||
    ("dPsalleOp CDailyCheckList choose_moment_edit"|gconf && ($check_list->_ref_list_type->type == "ouverture_sspi" || $check_list->_ref_list_type->type == "ouverture_preop")))
  || $check_list->_ref_list_type->type == "intervention"}}
    <input type="hidden" name="callback" value="refreshCheckList{{$check_list->type}}_{{$check_list->list_type_id}}" />
  {{else}}
    <input type="hidden" name="callback" value="saveCheckListIdCallback{{$check_list->type}}_{{$check_list->list_type_id}}" />
  {{/if}}

  {{if !in_array($check_list->object_class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes)}}
    <div class="small-info">Veuillez effectuer la vérification journalière pour <strong>{{$check_list->_ref_object}}</strong> grâce au formulaire suivant.</div>
  {{/if}}

  <table class="main tbl">
    {{assign var=category_id value=0}}
    {{foreach from=$check_list->_ref_item_types item=curr_type name="loop_items_types"}}
      {{assign var=red_code value='Ox\Mediboard\SalleOp\CDailyCheckList::itemCanRedCode'|static_call:$check_list->object_class:$check_list->type:$smarty.foreach.loop_items_types.index}}
      {{assign var=curr_cat value=$curr_type->category_id}}
      {{if array_key_exists($curr_cat, $check_item_categories)}}
      {{if $curr_type->category_id != $category_id}}
        <tr>
          <th colspan="2" class="text category" style="text-align: left;">
            <strong>{{$check_item_categories.$curr_cat->title}}</strong>
            {{if $check_item_categories.$curr_cat->desc}}
              &ndash; {{$check_item_categories.$curr_cat->desc}}
            {{/if}}
          </th>
        </tr>
      {{/if}}
      <tr>
        <td class="text">
          <ul style="padding-left: 0; list-style-position: inside;">
            <li>{{mb_value object=$curr_type field=title}}</li>
          </ul>

          {{if $curr_type->desc}}
            <div class="compact" style="margin-top: 0.5em;">{{mb_value object=$curr_type field=desc}}</div>
          {{/if}}
        </td>
        <td style="text-align: left;">
          {{assign var=attr value=$curr_type->attribute}}
          {{mb_ternary var=default_value test=$check_list->code_red value=null other=$curr_type->default_value}}

         {{if $curr_type->default_value == "yes"}}
              {{mb_include module=salleOp template=inc_check_list_field_yes}}
              {{mb_include module=salleOp template=inc_check_list_field_no}}
          {{else}}
              {{mb_include module=salleOp template=inc_check_list_field_no}}
              {{mb_include module=salleOp template=inc_check_list_field_yes}}
          {{/if}}

            {{if $attr == "notrecommended"}}
            <div>
              {{mb_include module=salleOp template=inc_check_list_field_notrecommended}}
            </div>
          {{elseif $attr == "notapplicable"}}
            <div>
              {{mb_include module=salleOp template=inc_check_list_field_notapplicable}}
            </div>
          {{elseif $attr == "texte"}}
            <div>
              {{mb_include module=salleOp template=inc_check_list_field_texte see=checkbox}}
            </div>
          {{/if}}
        </td>
      </tr>
        {{if $attr == "texte"}}
          <tr>
            <td colspan="2">
              <div>
                {{mb_include module=salleOp template=inc_check_list_field_texte see=commentaire}}
              </div>
            </td>
          </tr>
        {{/if}}
      {{/if}}
      {{assign var=category_id value=$curr_type->category_id}}
    {{foreachelse}}
      <tr>
        <td colspan="2" class="empty">{{tr}}CDailyCheckItemType.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{if !in_array($check_list->object_class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes) || $last_item
          || in_array($check_list->type, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_last_types)}}
      <tr>
        <td colspan="2" class="text">
          {{assign var=field_comments value=comments}}
          {{if isset($name_checklist|smarty:nodefaults) && in_array($name_checklist, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_comments_other)}}
            {{assign var=field_comments value=comments_other}}
          {{/if}}
          <hr />
          {{mb_label object=$check_list field=comments text="CDailyCheckList-$field_comments"}}<br />
          {{mb_field object=$check_list field=comments prop="text helped" aidesaisie="validateOnBlur: 0" form="$name_form_checklist" onchange="EditDailyCheck.submitCheckList(this.form,true)"}}
        </td>
      </tr>
    {{/if}}
    {{if $check_list->type == "postop_2016" || $check_list->_ref_list_type->alert_child}}
      {{mb_include module=salleOp template=alert_child_cheklist}}
    {{/if}}

    {{if in_array($check_list->object_class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes) && $check_list->type == "preop_2016" || $check_list->_ref_list_type->decision_go}}
      <tr>
        <td colspan="2" class="text">
          <hr />
          <h2 style="text-align: center;font-weight: bold;">{{mb_label object=$check_list field=decision_go}}</h2>
          <label style="font-size: 1.2em;color:green;margin-left: 20%;">
            <input type="radio" name="decision_go" value="go" {{if $check_list->decision_go == "go"}}checked{{/if}}
                   onchange="EditDailyCheck.changeGoIncision(this.form, true);"/>
            {{tr}}CDailyCheckList.decision_go.go{{/tr}}
          </label>
          <br/>
          <label style="font-size: 1.2em;color:red;margin-left: 20%;">
            <input type="radio" name="decision_go" value="nogo" {{if $check_list->decision_go == "nogo"}}checked{{/if}}
                   onchange="EditDailyCheck.changeGoIncision(this.form, true);"/>
            {{tr}}CDailyCheckList.decision_go.nogo{{/tr}}
          </label>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="text compact">
          {{tr}}CDailyCheckList-result_nogo-desc{{/tr}}
          {{mb_field object=$check_list field=result_nogo typeEnum=radio onchange="EditDailyCheck.submitCheckList(this.form, true);"}}
          <input type="radio" name="result_nogo" value="" style="display: none;" onchange="EditDailyCheck.submitCheckList(this.form, true);"/>
        </td>
      </tr>
      <script>
        Main.add(function(){
          EditDailyCheck.changeGoIncision(getForm('{{$name_form_checklist}}'), false);
        });
      </script>
    {{/if}}
    <tr>
      <td colspan="2" class="button">
        <label for="validator_id" style="display: none;">{{tr}}CDailyCheckList-validator_id{{/tr}}</label>
        <select name="validator_id" class="notNull ref" style="width: 10em;" onchange="EditDailyCheck.changeValidator(this, {{$app->user_id}});">
          <option value="" disabled="disabled" selected="selected">&mdash; Validateur</option>
          {{assign var=type_validateur value='|'|explode:$check_list->_ref_list_type->type_validateur}}

          {{if $check_list->object_class == "COperation" && (!$check_list->list_type_id || in_array("chir_interv", $type_validateur))}}
            <optgroup label="Praticiens">
              {{assign var=_obj value=$check_list->_ref_object}}
              <option value="{{$_obj->_ref_chir->user_id}}" {{if $app->user_id == $_obj->_ref_chir->user_id}}selected="selected"{{/if}}>{{$_obj->_ref_chir}}</option>
              {{if $anesth_id && isset($anesth|smarty:nodefaults)}}
                <option value="{{$anesth->_id}}" {{if $app->user_id == $anesth->_id}}selected="selected"{{/if}}>{{$anesth}}</option>
              {{/if}}
            </optgroup>
          {{/if}}

          {{if !($check_list->object_class == "COperation" && !$check_list->list_type_id)}}
            {{if $list_chirs && (in_array("chir", $type_validateur) ||
            ($check_list->object_class != "CSalle" && $check_list->object_class != "CBlocOperatoire" && !$check_list->list_type_id))}}
              <optgroup label="Chirurgiens">
                {{mb_include module=mediusers template=inc_options_mediuser list=$list_chirs selected=$app->user_id}}
              </optgroup>
            {{/if}}
            {{if $list_anesths && (in_array("anesth", $type_validateur) ||
            ($check_list->object_class != "CSalle" && $check_list->object_class != "CBlocOperatoire" && !$check_list->list_type_id))}}
              <optgroup label="Anesthésistes">
                {{mb_include module=mediusers template=inc_options_mediuser list=$list_anesths selected=$app->user_id}}
              </optgroup>
            {{/if}}
          {{/if}}
          <optgroup label="Personnel">
            {{if $check_list->object_class == "CSalle" || $check_list->object_class == "CBlocOperatoire" || ($check_list->object_class == "COperation" && $check_list->list_type_id)}}
              {{foreach from=$personnel item=curr_personnel}}
                {{assign var=emplacement_valide value=false}}
                {{foreach from=$curr_personnel->_emplacements item=_emplacement}}
                  {{if in_array($_emplacement, $type_validateur)}}
                    {{assign var=emplacement_valide value=true}}
                  {{/if}}
                {{/foreach}}
                {{if $emplacement_valide}}
                  {{assign var=curr_user value=$curr_personnel->_ref_user}}
                  <option value="{{$curr_user->_id}}" {{if $app->user_id == $curr_user->_id}}selected="selected"{{/if}}>{{$curr_user->_view}}</option>
                {{/if}}
              {{/foreach}}
            {{else}}
              {{foreach from=$personnel item=curr_personnel}}
                {{assign var=curr_user value=$curr_personnel->_ref_user}}
                <option value="{{$curr_user->_id}}" {{if $app->user_id == $curr_user->_id}}selected="selected"{{/if}}>{{$curr_user->_view}}</option>
              {{/foreach}}
            {{/if}}
          </optgroup>
        </select>
        {{assign var=entree_reelle value=1}}
        {{if $check_list->object_class == "COperation"}}
          {{assign var=entree_reelle value=$check_list->_ref_object->_ref_sejour->entree_reelle}}
        {{/if}}
        <label for="_validator_password" style="display: none;">{{tr}}CDailyCheckList-_validator_password{{/tr}}</label>
        <input type="password" class="notNull str" size="10" maxlength="32" name="_validator_password" {{if !$entree_reelle}}disabled="disabled"{{/if}}/>
        <button type="button" class="tick" onclick="EditDailyCheck.submitCheckList(this.form, null)" {{if !$entree_reelle}}disabled="disabled"{{/if}}>
          {{tr}}common-action-Sign{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
