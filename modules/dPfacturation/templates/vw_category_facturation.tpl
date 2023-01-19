{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=category_facturation ajax=true}}
<script>
  Main.add(function() {
    CategoryFactu.refeshList();
  });
</script>

{{assign var=function_id value=$app->_ref_user->function_id}}
{{if $app->_ref_user->isAdmin()}}
  {{assign var=function_id value=null}}
{{/if}}

<form action="?" name="selectPratCategoryfactu" method="get">
  <select name="function_id" onchange="CategoryFactu.refeshList();">
    <option value="">&mdash; {{tr}}CFunctions.select{{/tr}}</option>
    {{mb_include module=mediusers template=inc_options_function selected=$function_id list=$functions}}
  </select>
</form>
<div id="list_category_facturation"></div>
