{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    IdentityProofType.getTrustLevel(getForm("edit{{$type->_guid}}").trust_level);
  });
</script>

<form name="edit{{$type->_guid}}" action="?" method="post" onsubmit="return IdentityProofType.save(this);">
    {{mb_class object=$type}}
    {{mb_key object=$type}}
    <input type="hidden" name="del" value="0">

    <table class="form">
        {{mb_include module=system template=inc_form_table_header object=$type}}

        <tr>
            {{me_form_field nb_cells=1 mb_object=$type mb_field=label}}
                {{mb_field object=$type field=label}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=1 mb_object=$type mb_field=code}}
            {{if $type->isEditable() || !$type->_id}}
                {{mb_field object=$type field=code}}
            {{else}}
                {{mb_field object=$type field=code readonly=true}}
            {{/if}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=1 mb_object=$type mb_field=trust_level}}
                {{mb_field object=$type field=trust_level onchange="IdentityProofType.getTrustLevel(this)"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_bool nb_cells=1 mb_object=$type mb_field=active}}
                {{mb_field object=$type field=active}}
            {{/me_form_bool}}
        </tr>
        <tr>
            {{me_form_bool nb_cells=1 mb_object=$type mb_field=validate_identity class="validate_identity"}}
                {{mb_field object=$type field=validate_identity}}
            {{/me_form_bool}}
        </tr>
        <tr>
            <td class="button">
                <button class="save me-primary" type="button" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
                {{if $type->_id && $type->isEditable()}}
                    <button type="button" class="trash" onclick="IdentityProofType.delete(this, true);">{{tr}}Delete{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
