{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style type="text/css">
  {{mb_include module=dPcompteRendu template='../css/print.css' header=4 footer=3 ignore_errors=true}}

  html {
    font-family: Arial, Helvetica, sans-serif;
  }

  .print td {
    font-size: 11px;
    font-family: Arial, Verdana, Geneva, Helvetica, sans-serif;
  }
</style>

{{foreach from=$orders item=_order name=list_orders}}
  {{mb_include module=stock template=inc_order_form}}

  {{if !$smarty.foreach.list_orders.last}}
    <hr style="border: 0; page-break-after: always;" />
  {{/if}}
{{/foreach}}