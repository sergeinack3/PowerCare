{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(function () {
        InseeFields.initCPVille("editFrm", "cp", "ville", null, null, "tel");
        changePagePrimaryUsers();
    });

    changePagePrimaryUsers = function (page) {
        new Url("mediusers", "ajax_list_mediusers")
          .addParam("function_id", '{{$function->_id}}')
          .addParam("page_function", page)
          .requestUpdate("CFunctions-back-users");
    }
</script>

<form name="editFrm" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete : Control.Modal.close});">
    {{if !$can->edit}}
        <input name="_locked" value="1" hidden="hidden"/>
    {{/if}}
    <input type="hidden" name="m" value="mediusers"/>
    <input type="hidden" name="dosql" value="do_functions_aed"/>
    <input type="hidden" name="del" value="0"/>

    {{mb_key object=$function}}

    <script>
        Main.add(Control.Tabs.create.curry('tabs-form', true));
    </script>

    <ul id="tabs-form" class="control_tabs">
        <li><a href="#CFunctions">{{tr}}CFunctions{{/tr}}</a></li>
        <li><a href="#CFunctions-back-users">{{tr}}CFunctions-back-users{{/tr}}</a></li>
        {{if "medimail"|module_active && $function->_id}}
          <li><a href="#CFunctions-mssante">{{tr}}CMedimailAccount{{/tr}}</a></li>
        {{/if}}
    </ul>

    <div id="CFunctions" style="display: none;">
        <table class="form">
            <tr>
                {{if $function->_id}}
                    <th class="title modify text" colspan="2">
                        {{mb_include module=system template=inc_object_notes      object=$function}}
                        {{mb_include module=system template=inc_object_idsante400 object=$function}}
                        {{mb_include module=system template=inc_object_history    object=$function}}
                        {{mb_include module=system template=inc_object_uf         object=$function}}
                        {{tr}}CFunctions-title-modify{{/tr}} '{{$function}}'
                    </th>
                {{else}}
                    <th class="title me-th-new" colspan="2">
                        {{tr}}CFunctions-title-create{{/tr}}
                    </th>
                {{/if}}
            </tr>
            <tr>
                <th>{{mb_label object=$function field="text"}}</th>
                <td>{{mb_field object=$function field="text"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="soustitre"}}</th>
                <td>{{mb_field object=$function field="soustitre"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="group_id"}}</th>
                <td>{{mb_field object=$function field="group_id" options=$groups}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="type"}}</th>
                <td>{{mb_field object=$function field="type"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="color"}}</th>
                <td>
                    {{mb_field object=$function field="color" form="editFrm"}}
                </td>
            </tr>

            <tr>
                <th>{{mb_label object=$function field=initials}}</th>
                <td>{{mb_field object=$function field=initials}}</td>
            </tr>

            <tr>
                <th>{{mb_label object=$function field="adresse"}}</th>
                <td>{{mb_field object=$function field="adresse"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="cp"}}</th>
                <td>{{mb_field object=$function field="cp"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="ville"}}</th>
                <td>{{mb_field object=$function field="ville"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="tel"}}</th>
                <td>{{mb_field object=$function field="tel"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="fax"}}</th>
                <td>{{mb_field object=$function field="fax"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="email"}}</th>
                <td>{{mb_field object=$function field="email"}}</td>
            </tr>

            <tr>
                <th>{{mb_label object=$function field=finess}}</th>
                <td>{{mb_field object=$function field=finess}}</td>
            </tr>

            <tr>
                <th>{{mb_label object=$function field=siret}}</th>
                <td>{{mb_field object=$function field=siret}}</td>
            </tr>

            <tr>
                <th>{{mb_label object=$function field="quotas"}}</th>
                <td>{{mb_field object=$function field="quotas"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="actif"}}</th>
                <td>{{mb_field object=$function field="actif"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="compta_partagee"}}</th>
                <td>{{mb_field object=$function field="compta_partagee"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="consults_events_partagees"}}</th>
                <td>{{mb_field object=$function field="consults_events_partagees"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="admission_auto"}}</th>
                <td>{{mb_field object=$function field="admission_auto"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="facturable"}}</th>
                <td>{{mb_field object=$function field="facturable"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$function field="create_sejour_consult"}}</th>
                <td>{{mb_field object=$function field="create_sejour_consult"}}</td>
            </tr>
            <tr>
                <td class="button" colspan="2">
                    {{if $function->function_id}}
                        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                        <button class="trash" type="button" onclick="confirmDeletion(this.form,
                          {typeName:'la fonction',objName:'{{$function->text|smarty:nodefaults|JSAttribute}}'})">
                            {{tr}}Delete{{/tr}}
                        </button>
                    {{else}}
                        <button class="submit" name="btnFuseAction" type="submit">{{tr}}Create{{/tr}}</button>
                    {{/if}}
                </td>
            </tr>
        </table>
    </div>
</form>

<div id="CFunctions-back-users" style="display: none;" class="me-padding-0 me-no-border"></div>

{{if "medimail"|module_active && $function->_id}}
  <div id="CFunctions-mssante" class="me-padding-0">
      {{mb_include module=medimail template=inc_edit_account account=$function->_ref_medimail_account}}
  </div>
{{/if}}
