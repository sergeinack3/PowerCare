{{*
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('rpps-configure-tabs', true);
  });
</script>

{{if !$is_downloadable}}
  <div class="small-error">
    {{tr}}CRppsFileDownloader-Error-Cannot download file{{/tr}}
    <br/>
    {{tr}}CRppsFileDownloader-Error-Open route{{/tr}} : https://service.annuaire.sante.fr/annuaire-sante-webservices/V300/services/extraction/PS_LibreAcces
  </div>
{{/if}}

<ul class="control_tabs" id="rpps-configure-tabs">
  <li><a href="#tab-configuration">{{tr}}Config{{/tr}}</a></li>
  <li><a href="#tab-datasource-configuration">{{tr}}CSQLDataSource{{/tr}}</a></li>
</ul>

<div id="tab-configuration" style="display: none;">
  <form name="editConfig" action="?m=rpps&amp;{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
    {{mb_configure module=$m}}

    <table class="form">
      <tr>
        <th class="title" colspan="2">Configuration</th>
      </tr>

      {{mb_include module=system template=inc_config_str var=download_directory size=100}}
      {{mb_include module=system template=inc_config_num var=sync_step}}
      {{mb_include module=system template=inc_config_num var=disable_days_withtout_update}}

      <tr>
        <td class="button" colspan="2">
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="tab-datasource-configuration" style="display: none;">
  {{mb_include module=rpps template=inc_config_ds}}
</div>
