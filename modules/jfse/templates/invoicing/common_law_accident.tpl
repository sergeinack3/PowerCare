{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(() => Invoicing.setCommonLawAccidentNotNull(getForm('common_law_accident')));
</script>

<form name="common_law_accident" method="post" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
    <input type="hidden" name="t" value="{{$common_law->common_law_accident}}">

    <table style="width: 100%;">
        <tr>
            <td class="me-padding-5">
                {{me_form_field nb_cells=0 mb_object=$common_law mb_field=common_law_accident layout=true}}
                    {{*mb_field object=$common_law field=common_law_accident onchange="Invoicing.onCommonLawAccidentChange(this);"*}}
                    <label for="common_law_accident_1">
                        <input type="radio" name="common_law_accident" value="1" class="bool" id="common_law_accident_1"{{if $common_law->common_law_accident === '1'}} checked="checked"{{/if}} onchange="Invoicing.onCommonLawAccidentChange(this);">
                        {{tr}}Yes{{/tr}}
                    </label>
                    <label for="common_law_accident_0" class="me-padding-left-5">
                        <input type="radio" name="common_law_accident" value="0" class="bool" id="common_law_accident_0"{{if $common_law->common_law_accident === '0'}} checked="checked"{{/if}} onchange="Invoicing.onCommonLawAccidentChange(this);">
                        {{tr}}No{{/tr}}
                    </label>
                {{/me_form_field}}
            </td>
            <td id="date_common_law_accident-container"{{if !$common_law->common_law_accident}} style="display: none;"{{/if}} class="me-padding-5">
                {{me_form_field nb_cells=0 mb_object=$common_law mb_field=date}}
                    {{mb_field object=$common_law field=date register=true form=common_law_accident onchange="Invoicing.saveCommonLawAccident(this.form);"}}
                {{/me_form_field}}
            </td>
        </tr>
    </table>
    <div id="common_law_accident_messages_container" style="display: none;"></div>
</form>
