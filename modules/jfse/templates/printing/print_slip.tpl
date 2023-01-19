{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Printing ajax=$ajax}}

<form name="print-slip" method="post" onsubmit="return false;">
    <table>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$print_conf mb_field=mode}}
            {{mb_field object=$print_conf field=mode}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$print_conf mb_field=degraded layout=true}}
            {{mb_field object=$print_conf field=degraded}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=1 mb_object=$print_conf mb_field=date_min}}
            {{mb_field object=$print_conf field=date_min register=true form='print-slip'}}
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$print_conf mb_field=date_max}}
            {{mb_field object=$print_conf field=date_max register=true form='print-slip'}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='CPrintingSlipConf-batch' layout=true}}
                // TODO //
                Lot 1
                <input type="checkbox" name="batch" value="1">
                Lot 2
                <input type="checkbox" name="batch" value="2">
                Lot 3
                <input type="checkbox" name="batch" value="3">
                Lot 4
                <input type="checkbox" name="batch" value="4">
                Lot 5
                <input type="checkbox" name="batch">
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='CPrintingSlipConf-files' layout=true}}
                // TODO //
                Fichier 1
                <input type="checkbox" name="files" value="1">
                Fichier 2
                <input type="checkbox" name="files" value="2">
                Fichier 3
                <input type="checkbox" name="files" value="3">
                Fichier 4
                <input type="checkbox" name="files" value="4">
                Fichier 5
                <input type="checkbox" name="files">
            {{/me_form_field}}
        </tr>

        <tr>
            <td colspan="2">
                <button class="print" type="button"
                        onclick="Printing.printSlip(this.form)">
                    {{tr}}PrintingSlipController-Print slip{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
