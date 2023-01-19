{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  toggleItems = function (button, item_uid) {
    var elements = Array.from(document.getElementsByClassName(item_uid));
    if (button.classList.contains('fa-chevron-circle-up')) {
      elements.forEach(function (element) {
        element.style.display = "none";
      });
      button.classList.remove('fa-chevron-circle-up');
      button.classList.add('fa-chevron-circle-down')
    } else {
      elements.forEach(function (element) {
        element.style.display = "table-row";
      });
      button.classList.remove('fa-chevron-circle-down');
      button.classList.add('fa-chevron-circle-up')
    }
  }
</script>

<h1>{{tr}}CReport{{/tr}}</h1>

<table class="tbl">
  <tr>
    <th class="narrow">{{tr}}CItemReport-severity{{/tr}}</th>
    <th colspan="3">{{tr}}CItemReport-data{{/tr}}</th>
  </tr>

  {{foreach from=$report->getItems() item=_item}}
    {{unique_id var=item_uniqid}}
    {{assign var=hasSubItems value=$_item->getSubItems()}}

    {{* items *}}
    {{mb_include module=eai template='report/inc_report_item' item=$_item}}

    {{* sub_items *}}
    {{foreach from=$_item->getSubItems() item=subItem}}
      {{mb_include module=eai template='report/inc_report_item' item=$subItem is_subitem=1}}
    {{/foreach}}

  {{/foreach}}

</table>
