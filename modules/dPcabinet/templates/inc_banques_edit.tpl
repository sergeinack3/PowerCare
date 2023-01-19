{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete ajax=1}}

<script>
  Main.add(function () {
    InseeFields.initCPVille("editFrm", "cp", "ville");
  });
</script>

<form name="editFrm" method="post" onsubmit="return BanqueEdit.save(this);">
    {{mb_class object=$bank}}
    {{mb_key   object=$bank}}
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="callback" value="BanqueEdit.setValue"/>
    <table class="form">
        {{mb_include module=system template=inc_form_table_header object=$bank}}
        <tr>
            <th>{{mb_label object=$bank field="nom"}}</th>
            <td>{{mb_field object=$bank field="nom"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$bank field="description"}}</th>
            <td>{{mb_field object=$bank field="description"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$bank field="departement"}}</th>
            <td>{{mb_field object=$bank field="departement"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$bank field="boite_postale"}}</th>
            <td>{{mb_field object=$bank field="boite_postale"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$bank field="adresse"}}</th>
            <td>{{mb_field object=$bank field="adresse"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$bank field="cp"}}</th>
            <td>{{mb_field object=$bank field="cp"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$bank field="ville"}}</th>
            <td>{{mb_field object=$bank field="ville"}}</td>
        </tr>
        <tr>
            <td class="button" colspan="2">
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                {{if $bank->_id}}
                    <button class="trash" type="button"
                            onclick="BanqueEdit.delete(this.form,{typeName:'la banque ',objName:'{{$bank->nom|smarty:nodefaults|JSAttribute}}'})">
                        {{tr}}Delete{{/tr}}
                    </button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
