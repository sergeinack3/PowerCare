{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Printing ajax=$ajax}}

<form name="print-cerfa" method="post" onsubmit="return false;">
    {{mb_field object=$print_conf field=invoice_id hidden=true}}
    {{mb_field object=$print_conf field=invoice_number hidden=true}}

    <table>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$print_conf mb_field=duplicate layout=true}}
            {{mb_field object=$print_conf field=duplicate}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$print_conf mb_field=use_signature layout=true}}
            {{mb_field object=$print_conf field=use_signature}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=1 mb_object=$print_conf mb_field=use_background layout=true}}
            {{mb_field object=$print_conf field=use_background}}
            {{/me_form_field}}
        </tr>

        <tr>
            <td colspan="2">
                <button class="print" type="button"
                        onclick="Printing.printCerfa(this.form)">
                    {{tr}}PrintingSlipController-Print cerfa{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
