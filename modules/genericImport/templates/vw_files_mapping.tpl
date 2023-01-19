{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=genericImport script=generic_import ajax=true}}

<div class="campaign-select">
  {{mb_include module=import template=inc_vw_import_campaign_select campaigns=$campaigns last_campaign_id=$current_campaign_id}}

  <br/>

  <button style="margin-top: 10px; margin-bottom: 10px;" type="button" class="search" onclick="GenericImport.listImportFiles();">{{tr}}Search{{/tr}}</button>
</div>

<div id="result-list-files-for-campaign"></div>
