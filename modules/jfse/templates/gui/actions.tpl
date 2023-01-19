{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main me-w50 me-align-auto">
    <tr>
        <td>
            <button type="button" class="big" onclick="Cps.displayData();">{{tr}}CCpsCard-action-read{{/tr}}</button>
        </td>
        <td>
            <button type="button" class="big" onclick="JfseGui.showExportPayments();">{{tr}}NoemiePayments-action-export{{/tr}}</button>
        </td>
        <td>
            <button class="big" onclick="JfseGui.manageTLA();">{{tr}}jfse-gui-Manage TLA{{/tr}}</button>
        </td>
        <td>
            <button class="big" onclick="JfseGui.moduleVersion();">{{tr}}jfse-gui-Module version{{/tr}}</button>
        </td>
        <td>
            <button class="big" onclick="JfseGui.apiVersion();">{{tr}}jfse-gui-Api version{{/tr}}</button>
        </td>
        <td>
            <button class="big" onclick="JfseGui.mbVersion();">{{tr}}jfse-gui-Mb version{{/tr}}</button>
        </td>
    </tr>
</table>
