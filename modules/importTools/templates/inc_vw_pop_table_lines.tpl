{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display_all value=1}}

{{if $dsn === 'std' || $dsn === 'slave'}}
  {{assign var=display_all value=0}}
{{/if}}

<div id="table-data-search">
  <table class="main tbl">
    <tr>
      <td>
        {{mb_include module=system template=inc_pagination total=$total current=$start change_page='DatabaseExplorer.changePage'  step=$count}}
      </td>
    </tr>
  </table>

  {{mb_include template=inc_vw_table_lines search=true}}
</div>