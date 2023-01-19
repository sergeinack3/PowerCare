{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function() {
        Calendar.regField(getForm('editActeNGAP-accord_prealable{{$view}}').elements['date_demande_accord'], {limit: {stop: '{{'Ox\Core\CMbDT::date'|static_call:$act_ngap->execution}}'}});
    });
</script>

<table class="form me-padding-0 me-margin-0 me-max-width-100" style="width: 100%!important;">
    <tr>
        <td>
            <form name="editActeNGAP-accord_prealable{{$view}}" action="?" method="post"
                  onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
                {{mb_key object=$act_ngap}}
                {{mb_class object=$act_ngap}}
                <table class="form me-no-box-shadow">
                    <tr>
                        <td>
                            {{me_form_field layout=true mb_object=$act_ngap mb_field="accord_prealable"}}
                                {{mb_field object=$act_ngap field="accord_prealable" typeEnum=radio readonly=$readonly
                                value=$dep
                                onchange="ActesNGAP.toggleDateDEP(this, '$view'); ActesNGAP.checkDEP('$view');"}}
                            {{/me_form_field}}
                        </td>
                    </tr>
                    <tbody id="accord_infos{{$view}}"{{if !$act_ngap->accord_prealable}} style="display: none;"{{/if}}>
                        <tr>
                            <td>
                                {{me_form_field mb_object=$act_ngap mb_field="date_demande_accord"}}
                                    {{mb_field object=$act_ngap field="date_demande_accord" value=$date_request_agreement onchange="ActesNGAP.checkDEP('$view');" readonly=$readonly}}
                                {{/me_form_field}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{me_form_field mb_object=$act_ngap mb_field="reponse_accord"}}
                                    {{mb_field object=$act_ngap field="reponse_accord" value=$response_agreement onchange="ActesNGAP.checkDEP('$view');" emptyLabel='Select' readonly=$readonly}}
                                {{/me_form_field}}
                            </td>
                        </tr>
                    </tbody>
                    <tr>
                        <td colspan="2" class="me-text-align-center">
                            {{if $readonly}}
                                <button type="button" class="me-primary cancel" onclick="Control.Modal.close();">
                                    {{tr}}Close{{/tr}}
                                </button>
                            {{else}}
                                <button type="button" class="me-primary tick singleclick"
                                    onclick="{{if !$act_ngap->_id}}ActesNGAP.submitDEP('{{$view}}');{{else}}this.form.onsubmit();{{/if}}">
                                    {{tr}}Validate{{/tr}}
                                </button>
                                <button type="button" class="me-tertiary cancel" onclick="Control.Modal.close();">
                                    {{tr}}Cancel{{/tr}}
                                </button>
                            {{/if}}
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
