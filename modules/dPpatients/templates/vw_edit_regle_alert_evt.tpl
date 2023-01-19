{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM ajax=true}}

<script>
  Main.add(function() {
    var form = getForm("edit_program");
    form.nb_anticipation.addSpinner({min:1, max:5,step:1});
    form.periode_refractaire.addSpinner({min:1, max:5,step:1});
    {{foreach from=$regle->_ext_diagnostics item=_cim}}
      RegleEvt.createSpanCIM('{{$_cim->code}}', 'diagnostic');
    {{/foreach}}

    {{foreach from=$regle->_ext_pathologies item=_cim}}
      RegleEvt.createSpanCIM('{{$_cim->code}}', 'pathologie');
    {{/foreach}}

    CIM.autocomplete(getForm('edit_program').keywords_code_diagnostic, null, {
      afterUpdateElement: function(input) {
        $V(getForm('edit_program')._added_code_cim_diagnostic, input.value);
      }
    });

    CIM.autocomplete(getForm('edit_program').keywords_code_pathologie, null, {
      afterUpdateElement: function(input) {
        $V(getForm('edit_program')._added_code_cim_pathologie, input.value);
      }
    });

    var modal = form.up("div.content").up("div.modal");
    modal.setStyle({overflow: "visible"});
    modal.down('div.content').setStyle({overflow: "visible"});
  });
</script>

<form name="edit_program" method="post" onsubmit="return onSubmitFormAjax(this, function() {Control.Modal.close();});">
  {{mb_key object=$regle}}
  {{mb_class object=$regle}}
  {{mb_field object=$regle field=diagnostics hidden=1}}
  {{mb_field object=$regle field=pathologies hidden=1}}
  <input type="hidden" name="_praticien_id" value="{{$app->user_id}}" />
  <input type="hidden" name="del" value="" />
  {{if !$regle->_id}}
    <input type="hidden" name="callback" value="RegleEvt.editRegle"/>
  {{/if}}
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$regle show_notes=false}}
    <tr>
      <th>{{mb_label object=$regle field=group_id}}</th>
      <td>
        {{mb_field object=$regle field=group_id form="edit_program" autocomplete="true,1,50,true,true"}}
        <button type="button" class="cancel notext me-tertiary me-dark" title="{{tr}}Clear{{/tr}}"
                onclick="$V(this.form.group_id, '');$V(this.form.group_id_autocomplete_view, '');"></button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$regle field=function_id}}</th>
      <td>
        {{mb_field object=$regle field=function_id form="edit_program" autocomplete="true,1,50,true,true"}}
        <button type="button" class="cancel notext me-tertiary me-dark" title="{{tr}}Clear{{/tr}}"
                onclick="$V(this.form.function_id, '');$V(this.form.function_id_autocomplete_view, '');"></button>
      </td>
    </tr>
    {{if !in_array($app->user_prefs.UISTYLE, array("tamm", "pluus"))}}
      <tr>
        <th>{{mb_label object=$regle field=user_id}}</th>
        <td>
          {{mb_field object=$regle field=user_id form="edit_program" autocomplete="true,1,50,true,true"}}
          <button type="button" class="cancel notext me-tertiary me-dark" title="{{tr}}Clear{{/tr}}"
                  onclick="$V(this.form.user_id, '');$V(this.form.user_id_autocomplete_view, '');"></button>
        </td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label class=$regle field=name}}</th>
      <td>{{mb_field object=$regle field=name}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=age_operateur}}</th>
      <td>{{mb_field object=$regle field=age_operateur emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=age_valeur}}</th>
      <td>{{mb_field object=$regle field=age_valeur}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=sexe}}</th>
      <td>{{mb_field object=$regle field=sexe emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=diagnostics}}</th>
      <td>
        <input type="text" name="keywords_code_diagnostic" class="autocomplete str" value="" size="10" />
        <input type="hidden" name="_added_code_cim_diagnostic" onchange="console.log('wtf'); RegleEvt.createSpanCIM($V(this), 'diagnostic'); $V(this, '');" />
        <button class="search notext" type="button"
                onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_cim_diagnostic']), this.form.elements['_praticien_id']);">
          {{tr}}Search{{/tr}}
        </button>
        <div id="codes_cim_regle_alerte_diagnostic"></div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=pathologies}}</th>
      <td>
        <input type="text" name="keywords_code_pathologie" class="autocomplete str" value="" size="10" />
        <input type="hidden" name="_added_code_cim_pathologie" onchange="RegleEvt.createSpanCIM($V(this), 'pathologie'); $V(this, '');" />
        <button class="search notext" type="button"
                onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_cim_pathologie']), this.form.elements['_praticien_id']);">
            {{tr}}Search{{/tr}}
        </button>
        <div id="codes_cim_regle_alerte_pathologie"></div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=ald typeEnum=checkbox}}</th>
      <td>{{mb_field object=$regle field=ald typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=programme_clinique_id}}</th>
      <td>
        {{if $programmes|@count}}
          {{mb_field object=$regle field=programme_clinique_id options=$programmes}}
        {{else}}
          <span class="empty">{{tr}}None{{/tr}}</span>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=nb_anticipation}}</th>
      <td>{{mb_field object=$regle field=nb_anticipation}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=periode_refractaire}}</th>
      <td>{{mb_field object=$regle field=periode_refractaire}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=type_alerte}}</th>
      <td>{{mb_field object=$regle field=type_alerte}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=$regle field=actif}}</th>
      <td>{{mb_field object=$regle field=actif}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if !$regle->_id}}
          <button type="button" class="save" onclick="RegleEvt.compactCodeCIM();">{{tr}}Save{{/tr}}</button>
        {{else}}
          <button type="button" class="save" onclick="RegleEvt.compactCodeCIM();">{{tr}}Edit{{/tr}}</button>
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true}, {onComplete: Control.Modal.close})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
{{if $regle->_id}}
  <script>
    Main.add(function() {
      var form = getForm('add-CEvenementAlerteUser');
      new Url('mediusers', 'ajax_users_autocomplete')
        .addParam('edit', '1')
        .autoComplete(form._user_view, null, {
          minChars: 0,
          method: "get",
          select: "view",
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            var id = selected.getAttribute("id").split("-")[2];
            $V(form.user_id, id);
          }
        }
      );
    });
  </script>
  <table class="main form">
    <tr>
      <th class="title">{{tr}}CEvenementAlerteUser.all{{/tr}}</th>
    </tr>
    <tr>
      <td class="button">
        <form name="add-CEvenementAlerteUser" method="post"
              onsubmit="return onSubmitFormAjax(this, function() {Control.Modal.refresh();});">
          {{mb_class class=CEvenementAlerteUser}}
          <input type="hidden" name="object_id" value="{{$regle->_id}}" />
          <input type="hidden" name="object_class" value="{{$regle->_class}}" />
          <input type="hidden" name="user_id" value="" />
          <input type="hidden" name="function_id" value="{{$app->_ref_user->function_id}}" />
          <input type="text" name="_user_view" value="" class="autocomplete" />
          <button type="button" class="add notext" title="{{tr}}Add{{/tr}}" onclick="this.form.onsubmit();"></button>
        </form>
      </td>
    </tr>
    {{foreach from=$regle->_ref_users_evt item=_user_evt}}
      <tr>
        <td>
          <form name="delete-{{$_user_evt->_guid}}" method="post"
                onsubmit="return onSubmitFormAjax(this, function() {Control.Modal.refresh();});">
            {{mb_key object=$_user_evt}}
            {{mb_class object=$_user_evt}}
            <input type="hidden" name="del" value="1" />
            <button type="button" class="trash notext" title="{{tr}}Delete{{/tr}}" onclick="this.form.onsubmit();"></button>
          </form>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_evt->_ref_user}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty">{{tr}}None{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}
