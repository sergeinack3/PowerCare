{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("edit-geste-perop");

    GestePerop.userAutocomplete(form);
    GestePerop.functionAutocomplete(form);
    GestePerop.groupAutocomplete(form);
    GestePerop.showCIMs10(null, 'GestePerop.addAntecedent(this);');
    GestePerop.incidentAntecedent(form, '{{$geste_perop->antecedent_code_cim}}');

    $("precisions").fixedTableHeaders();
  });
</script>

{{assign var=file value=$geste_perop->_ref_file}}

{{if $geste_perop->_id && $file->_id}}
  <form name="editFile{{$file->_id}}" action="?" target="#" method="post"
        onsubmit="return onSubmitFormAjax(this, function(){ Control.Modal.refresh(); });">
    {{mb_key   object=$file}}
    {{mb_class object=$file}}
    <input type="hidden" name="del" value="1" />
  </form>
{{/if}}

<form name="edit-geste-perop" method="post" action="?" enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this, function(){
  Control.Modal.close(); GestePerop.loadGestesPerop(getForm('filterGestePerop')); });">
  <input type="hidden" name="m" value="dPsalleOp" />
  {{mb_class object=$geste_perop}}
  {{mb_key object=$geste_perop}}

  {{mb_field object=$geste_perop field=antecedent_code_cim hidden=true}}

  <table class="main form me-small-form">
    {{mb_include module=system template=inc_form_table_header object=$geste_perop}}

    <tr>
      <th>{{mb_label object=$geste_perop field="user_id"}}</th>
      <td>
        {{mb_field object=$geste_perop field=user_id hidden=1
        onchange="
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="user_id_view" value="{{$geste_perop->_ref_user}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field="function_id"}}</th>
      <td>
        {{mb_field object=$geste_perop field=function_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="function_id_view" value="{{$geste_perop->_ref_function}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field="group_id"}}</th>
      <td>
        {{mb_field object=$geste_perop field=group_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }"}}
        <input type="text" name="group_id_view" value="{{$geste_perop->_ref_group}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field=libelle}}</th>
      <td>{{mb_field object=$geste_perop field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field=description}}</th>
      <td>{{mb_field object=$geste_perop field=description}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field=categorie_id}}</th>
      <td>{{mb_field object=$geste_perop field=categorie_id options=$evenement_categories style="width: 177px;"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field=incident}}</th>
      <td>{{mb_field object=$geste_perop field=incident typeEnum=radio}}</td>
    </tr>
    <tr id="show_make_antecedent">
      <td></td>
      <td>
        <input name="antecedent" type="checkbox" value="" onchange="GestePerop.incidentAntecedent(this.form);"/>
        {{tr}}CAntecedent-action-Make an antecedent{{/tr}}
      </td>
    </tr>
    <tr>
      <td></td>
      <td id="do_antecedent"></td>
    </tr>
    <tr>
      <th>{{mb_label object=$geste_perop field=actif}}</th>
      <td>{{mb_field object=$geste_perop field=actif}}</td>
    </tr>
    <tr>
      <th>
          <span title="{{tr}}CAnesthPeropCategorie-picture-desc{{/tr}}">
            {{tr}}CAnesthPeropCategorie-picture{{/tr}}
          </span>
      </th>
      <td>
        {{mb_include module=system template=inc_inline_upload}}

        {{if $geste_perop->_id && $file->_id}}
          <div style="padding-bottom: 15px;">
            <table class="form">
              <tr>
                <th class="section">{{tr}}CAnesthPeropCategorie-Preview of the associated image{{/tr}}</th>
              </tr>
              <tr>
                <td class="categorie_preview">
                  <div class="categorie_img">
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$file->_guid}}')">
                      {{thumbnail document=$file profile=medium style="max-width:120px;"}}
                    </span>
                    <span style="margin-right: 5px;">
                      <button type="button" class="trash" onclick="getForm('editFile{{$file->_id}}').onsubmit();" title="{{tr}}Delete{{/tr}}"></button>
                    </span>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        {{/if}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="button" class="save" onclick="if (GestePerop.checkContext(this.form)) {this.form.onsubmit();}">
          {{tr}}Save{{/tr}}
        </button>
        {{if $geste_perop->_id}}
          <button class="trash" type="button" onclick="
            confirmDeletion(this.form,
            {typeName: 'le geste perop', objName: '{{$geste_perop->libelle}}'}
            ,Control.Modal.close);
            ">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>

  </table>
</form>

<div id="precisions" style="margin-top: 15px;">
{{if $geste_perop->_id}}
 {{mb_include module=salleOp template=inc_vw_list_precisions precisions=$precisions}}
{{/if}}
</div>
