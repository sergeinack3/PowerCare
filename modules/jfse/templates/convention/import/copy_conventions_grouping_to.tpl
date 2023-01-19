{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" name="">
    <table class="main tbl">
        <tr>
            <th colspan="2">{{tr}}CConvention-importPS{{/tr}}</th>
        </tr>
        <tr>
            <th>{{tr}}CConvention-importPS-mode{{/tr}}</th>
            <td>
                <select name="mode" id="import_mode">
                    <option value="0">{{tr}}CConvention-importPS-mode.0{{/tr}}</option>
                    <option value="1">{{tr}}CConvention-importPS-mode.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-importPS-jfse_id_dest{{/tr}}</th>
            <td><input type="text" name="jfse_id"></td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-importPS-group_id_dest{{/tr}}</th>
            <td><input type="text" name="group_id"></td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="button" class="copy" onclick="Convention.importConventionsRegroupementsByPS(this.form)">
                    {{tr}}Copy{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
