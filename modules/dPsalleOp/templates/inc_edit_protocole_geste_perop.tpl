{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("editProtocoleGeste");
    GestePerop.userAutocomplete(form);
    GestePerop.functionAutocomplete(form);
    GestePerop.groupAutocomplete(form);
    GestePerop.gestePeropAutocomplete(form, getForm('protocoleGesteItem'), 'protocole', '{{$protocole_geste_perop->_guid}}');
  });
</script>

<form name="protocoleGesteItem" action="?" target="#" method="post"
      onsubmit="onSubmitFormAjax(this, GestePerop.refreshListProtocoleItems.curry('{{$protocole_geste_perop->_id}}'));">
  {{mb_key   object=$protocole_geste_perop_item}}
  {{mb_class object=$protocole_geste_perop_item}}

  <input type="hidden" name="protocole_geste_perop_id" value="{{$protocole_geste_perop->_id}}"/>
  <input type="hidden" name="object_id" value=""/>
  <input type="hidden" name="object_class" value=""/>
</form>

<form name="editProtocoleGeste" method="post" action="?" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="dPsalleOp"/>
  {{mb_class object=$protocole_geste_perop}}
  {{mb_key   object=$protocole_geste_perop}}

  {{if !$protocole_geste_perop->_id}}
    <input type="hidden" name="callback" value="GestePerop.editProtocoleGestePerop"/>
  {{/if}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$protocole_geste_perop}}

    <tr>
      <th>{{mb_label object=$protocole_geste_perop field="user_id"}}</th>
      <td>
        {{mb_field object=$protocole_geste_perop field=user_id hidden=1
        onchange="
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="user_id_view" value="{{$protocole_geste_perop->_ref_user}}"/>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$protocole_geste_perop field="function_id"}}</th>
      <td>
        {{mb_field object=$protocole_geste_perop field=function_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="function_id_view" value="{{$protocole_geste_perop->_ref_function}}"/>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$protocole_geste_perop field="group_id"}}</th>
      <td>
        {{mb_field object=$protocole_geste_perop field=group_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }"}}
        <input type="text" name="group_id_view" value="{{$protocole_geste_perop->_ref_group}}"/>
      </td>
    </tr>


    <tr>
      <th>{{mb_label object=$protocole_geste_perop field=libelle}}</th>
      <td>{{mb_field object=$protocole_geste_perop field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$protocole_geste_perop field=description}}</th>
      <td>{{mb_field object=$protocole_geste_perop field=description}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$protocole_geste_perop field=actif}}</th>
      <td>{{mb_field object=$protocole_geste_perop field=actif}}</td>
    </tr>

    {{if $protocole_geste_perop->_id}}
      <tr>
        <th
          title="{{tr}}CProtocoleGestePerop-Associate a Perop gesture with a protocol-desc{{/tr}}">{{tr}}CGestePerop{{/tr}}</th>
        <td>
          <input type="text" name="geste_perop_id_view" value=""/>
        </td>
      </tr>
      <tr>
        <th
          title="{{tr}}CProtocoleGestePerop-Associates a perop gesture category with a protocol-desc{{/tr}}">{{tr}}CAnesthPerop-categorie_id{{/tr}}</th>
        <td>
          <select name="categorie_id"
                  onchange="GestePerop.showListGestes(this.value, '{{$protocole_geste_perop->_id}}', 0);">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$evenement_categories item=_category}}
              <option value="{{$_category->_id}}">{{$_category->libelle}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    {{mb_include module=system template=inc_form_table_footer object=$protocole_geste_perop options="{typeName: 'le protocole de geste perop', objName: '`$protocole_geste_perop->libelle`'}" options_ajax="Control.Modal.close"}}
  </table>
</form>

{{if $protocole_geste_perop->_id}}
  <br/>
  <div id="list_items_{{$protocole_geste_perop->_id}}">
    {{mb_include module=salleOp template=inc_vw_protocole_geste_perop_items}}
  </div>
{{/if}}
