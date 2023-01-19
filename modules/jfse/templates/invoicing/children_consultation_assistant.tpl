{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(() => {
        Calendar.regField(getForm('ChildrenConsultationAssistant').elements['reference_date']);
    });
</script>

<div id="ChildrenConsultationAssistant-{{$invoice->id}}" style="display: none;">
    <form name="ChildrenConsultationAssistant" method="post" action="?" onsubmit="return false;">
        <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
        <table class="form">
            <tr>
                {{me_form_field label='CJfseChildrenConsultationAssistant-reference_date' title_label="CJfseChildrenConsultationAssistant-reference_date-desc" field_class=date nb_cells=1}}
                    <input type="hidden" name="reference_date" class="date notNull" value="{{$invoice->creation_date}}">
                {{/me_form_field}}
            </tr>
            {{if $invoice->user_interface->amendment_27_referring_physician}}
                <tr>
                    {{me_form_field layout=true label='CJfseChildrenConsultationAssistant-referring_physician' title_label="CJfseChildrenConsultationAssistant-referring_physician-desc" field_class=bool nb_cells=1}}
                        <label for="referring_physician_1">
                            <input type="radio" name="referring_physician" value="1" class="bool" id="referring_physician_1">
                            {{tr}}Yes{{/tr}}
                        </label>
                        <label for="referring_physician_0" style="margin-left: 5px;">
                            <input type="radio" name="referring_physician" value="0" class="bool" id="referring_physician_0">
                            {{tr}}No{{/tr}}
                        </label>
                    {{/me_form_field}}
                </tr>
            {{/if}}
            {{if $invoice->user_interface->amendment_27_enforceable_tariff}}
                <tr>
                    {{me_form_field layout=true label='CJfseChildrenConsultationAssistant-enforceable_tariff' title_label="CJfseChildrenConsultationAssistant-enforceable_tariff-desc" field_class=bool nb_cells=1}}
                        <label for="enforceable_tariff_1">
                            <input type="radio" name="enforceable_tariff" value="1" class="bool" id="enforceable_tariff_1">
                            {{tr}}Yes{{/tr}}
                        </label>
                        <label for="enforceable_tariff_0" style="margin-left: 5px;">
                            <input type="radio" name="enforceable_tariff" value="0" class="bool" id="enforceable_tariff_0">
                            {{tr}}No{{/tr}}
                        </label>
                    {{/me_form_field}}
                </tr>
            {{/if}}
            <tr id="children_consultation_assistant_results" style="display: none;">
                <td>

                </td>
            </tr>
            <tr>
                <td class="button">
                    <button type="button" class="tick me-primary" onclick="Invoicing.getChildrenConsultationAssistant(this.form);">{{tr}}Validate{{/tr}}</button>
                </td>
            </tr>
        </table>
    </form>
</div>
