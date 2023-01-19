{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=CMbObject_view}}

{{if $can->admin}}
  <form name="reglement-delete" action="?m={{$m}}" method="post" onsubmit="return confirm('Vraiment ?') && onSubmitFormAjax(this, function() { location.reload(); });">
    <input type="hidden" name="m" value="cabinet" />
    {{mb_class object=$object}}
    {{mb_key   object=$object}}
    <input type="hidden" name="del" value="1" />
    <button class="trash" type="submit">{{tr}}Delete{{/tr}}</button>
  </form>
{{/if}}
