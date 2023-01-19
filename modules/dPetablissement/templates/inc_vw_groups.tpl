{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete ajax=1}}

<script>
    Main.add(function () {
        InseeFields.initCPVille("group", "cp", "ville", null, null, "tel");

        Control.Tabs.create("tabs_groups");
    });
</script>

<form name="group" method="post" onsubmit="return checkForm(this);">
    {{mb_class object=$group}}
    {{mb_key   object=$group}}

  <ul id="tabs_groups" class="control_tabs">
    <li><a href="#identification">{{tr}}CGroups-title identification{{/tr}}</a></li>
    <li><a href="#iconographie">{{tr}}CGroups-title iconographie{{/tr}}</a></li>
    {{if "medimail"|module_active && $group->_id}}
      <li><a href="#mssante">{{tr}}CMedimailAccount{{/tr}}</a></li>
    {{/if}}
  </ul>

    <table id="identification" class="form" style="display: none;">
        {{mb_include module=system template=inc_form_table_header object=$group}}

        <tr>
            <th>{{mb_label object=$group field="_name"}}</th>
            <td>{{mb_field object=$group field="_name"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="oid"}}</th>
            <td>{{mb_field object=$group field="oid"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="code"}}</th>
            <td>{{mb_field object=$group field="code"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="description"}}</th>
            <td>{{mb_field object=$group field="description"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="raison_sociale"}}</th>
            <td>{{mb_field object=$group field="raison_sociale"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="adresse"}}</th>
            <td>{{mb_field object=$group field="adresse"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="cp"}}</th>
            <td>{{mb_field object=$group field="cp"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="ville"}}</th>
            <td>{{mb_field object=$group field="ville"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="tel"}}</th>
            <td>{{mb_field object=$group field="tel"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="fax"}}</th>
            <td>{{mb_field object=$group field="fax"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="tel_anesth"}}</th>
            <td>{{mb_field object=$group field="tel_anesth"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="mail"}}</th>
            <td>{{mb_field object=$group field="mail"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="mail_apicrypt"}}</th>
            <td>{{mb_field object=$group field="mail_apicrypt"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="web"}}</th>
            <td>{{mb_field object=$group field="web" size="35"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="directeur"}}</th>
            <td>{{mb_field object=$group field="directeur"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="domiciliation"}}</th>
            <td>{{mb_field object=$group field="domiciliation"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="siret"}}</th>
            <td>{{mb_field object=$group field="siret"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="finess"}}</th>
            <td>{{mb_field object=$group field="finess"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="ape"}}</th>
            <td>{{mb_field object=$group field="ape"}}</td>
        </tr>

        <tr>
            <th>{{mb_label object=$group field="lat"}}</th>
            <td>{{mb_field object=$group field="lat" style="width:150px;"}}</td>
        </tr>

        <tr>
            <th>{{mb_label object=$group field="lon"}}</th>
            <td>{{mb_field object=$group field="lon" style="width:150px;"}}</td>
        </tr>

        {{if $group->_id}}
            <tr>
                <th>{{mb_label object=$group field="service_urgences_id"}}</th>
                <td>
                    <select name="service_urgences_id">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{mb_include module=mediusers template=inc_options_function list=$group->_ref_functions selected=$group->service_urgences_id}}
                    </select>
                </td>
            </tr>
            <tr>
                <th>{{mb_label object=$group field="pharmacie_id"}}</th>
                <td>
                    <select name="pharmacie_id">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{mb_include module=mediusers template=inc_options_function list=$group->_ref_functions selected=$group->pharmacie_id}}
                    </select>
                </td>
            </tr>
        {{/if}}

        <tr>
            <th>{{mb_label object=$group field="chambre_particuliere"}}</th>
            <td>{{mb_field object=$group field="chambre_particuliere"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field=legal_entity_id}}</th>
            <td>
                <select name="legal_entity_id" style="width:14em; vertical-align: top">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    {{foreach from=$legal_entities item=_legal_entity}}
                        <option value="{{$_legal_entity->_id}}"
                                {{if $group->legal_entity_id == $_legal_entity->_id}}selected{{/if}}>{{$_legal_entity->name}}</option>
                    {{/foreach}}
                </select>
            </td>
        </tr>

        <tr>
            <th>{{mb_label object=$group field="opening_date"}}</th>
            <td>{{mb_field object=$group field="opening_date" form=group register=true}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="opening_reason"}}</th>
            <td>{{mb_field object=$group field="opening_reason"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="closing_date"}}</th>
            <td>{{mb_field object=$group field="closing_date" form=group register=true}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="closing_reason"}}</th>
            <td>{{mb_field object=$group field="closing_reason"}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="activation_date"}}</th>
            <td>{{mb_field object=$group field="activation_date" form=group register=true}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$group field="inactivation_date"}}</th>
            <td>{{mb_field object=$group field="inactivation_date" form=group register=true}}</td>
        </tr>

        <tr>
            <td class="button" colspan="2">
                {{if $group->_id}}
                    <button class="modify" type="submit" name="modify">
                        {{tr}}Save{{/tr}}
                    </button>
                    <button class="trash" type="button" name="delete"
                            onclick="confirmDeletion(this.form,{typeName:'l\'établissement', objName: $V(this.form._name)})">
                        {{tr}}Delete{{/tr}}
                    </button>
                {{else}}
                    <button class="new" type="submit" name="create">
                        {{tr}}Create{{/tr}}
                    </button>
                {{/if}}
            </td>
        </tr>
    </table>

    <table id="iconographie" class="form" style="display: none;">
        <tr>
            <th class="title">Logo</th>
        </tr>
        <tr>
            <td class="button">
                {{mb_include module=files template=inc_named_file object=$group name=CGroups_logo.jpg mode=edit}}
            </td>
        </tr>
    </table>
</form>

{{if "medimail"|module_active && $group->_id}}
    <div id="mssante" class="me-padding-0">
        {{mb_include module=medimail template=inc_edit_account account=$group->_ref_medimail_account}}
    </div>
{{/if}}
