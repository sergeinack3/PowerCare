{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="cps_code" style="display: none; text-align: center;">
    <table class="me-w100">
        <tr>
            <td>
                <input type="password" name="cps_code" pattern="/[0-9]{4}/" size="4" maxlength="4">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="button" class="tick" onclick="Control.Modal.close();">{{tr}}Validate{{/tr}}</button>
            </td>
        </tr>
    </table>
</div>
