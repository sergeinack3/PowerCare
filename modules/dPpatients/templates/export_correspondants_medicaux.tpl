{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="medicaux">
  <form name="medecin_csv" action="?" method="get" onsubmit="return onSubmitFormAjax(this);">
    <table class="main form">
      <tr>
        <th class="title" colspan="6">{{tr}}CMedecin.csv{{/tr}}</th>
      </tr>
      <tr>
        <td class="button" colspan="6">
          {{if $is_admin}}
            <a class="button download" href="?m=patients&raw=export_medecins_csv&emails=1"
               target="_blank">{{tr}}CMedecin-action-Export with e-mail address{{/tr}}</a>
            <a class="button download" href="?m=patients&raw=export_medecins_csv&emails=0"
               target="_blank">{{tr}}Export-CSV-by-search{{/tr}}</a>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
</div>

