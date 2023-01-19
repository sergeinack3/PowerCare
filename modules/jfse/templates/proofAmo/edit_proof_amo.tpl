{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=ProofAmo ajax=$ajax}}
{{mb_script module=jfse script=Jfse ajax=$ajax}}

<script type="text/javascript">
    Main.add(() => {
        ProofAmo.initializeView(getForm('proofAMO'));
    });
</script>

<form action="?" name="proofAMO" method="post" onsubmit="return false;">
    {{mb_field object=$proofAMO field=invoice_id hidden=true}}

    <table>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$proofAMO mb_field=nature class="me-padding-5"}}
                <select name="nature" onchange="ProofAmo.selectProofAmoType(this);">
                    <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                    {{foreach from=$types item=_type}}
                        <option value="{{$_type->code}}"{{if $_type->code == $proofAMO->nature}} selected="selected"{{/if}}>{{$_type->label}}</option>
                    {{/foreach}}
                </select>
            {{/me_form_field}}
        </tr>

        <tr>
            <td class="halfPane me-padding-5">
                <div id="proof_amo_date_container" style="display: none;">
                    {{me_form_field mb_object=$proofAMO mb_field=date class="me-padding-5"}}
                        {{mb_field object=$proofAMO field=date register=true form=proofAMO}}
                    {{/me_form_field}}
                </div>
            </td>
            <td class="halfPane me-padding-5">
                <div id="proof_amo_origin_container" style="display: none;">
                    {{me_form_field mb_object=$proofAMO mb_field=origin}}
                        {{mb_field object=$proofAMO field=origin}}
                    {{/me_form_field}}
                </div>
            </td>
        <tr>
            <td class="button me-padding-5" colspan="2">
                <div id="edit_proof_amo_message" style="display: none;"></div>
                <button type="button" class="save" onclick="ProofAmo.saveProof(this.form)">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
