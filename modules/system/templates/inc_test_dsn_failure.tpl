{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="error" style="display: inline-block">
  {{tr var1=$dsn var2=$host}}CSQLDataSource-msg-Failed to connect to %s on %s{{/tr}}
</div>

<button class="fa fa-database" type="button" onclick="DSN.createDB('{{$dsn}}', '{{$host}}')">
  {{tr}}CSQLDataSource-action-Create DB{{/tr}}
</button>
<br />