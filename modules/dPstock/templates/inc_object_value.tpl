{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_class == "CProductStockGroup" && $field == "bargraph"}}
  {{mb_include module=stock template=inc_bargraph stock=$object}}
{{else}}
  {{mb_value object=$object field=$field}}
{{/if}}