{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl" id="pattern-incrementer-legend" style="display: none; max-width: 600px;">
  <col style="width: 50px;"/>

  <tr>
    <th colspan="2" class="title">{{tr}}CIncrementer-pattern_legend{{/tr}}</th>
  </tr>

  {{foreach from=$object_vars key=_view item=_value}}
    <tr>
      <th>[{{$_view}}]</th>
      <td>{{tr}}CIncrementer-pattern.{{$_view}}{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>