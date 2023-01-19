{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default title=''}}
<!DOCTYPE html>
<head>
  <link href="./style/mediboard_ext/vendor/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style/mediboard_ext/main.css" type="text/css" media="screen"/>
  <title>
    {{if !$title}}
      {{tr}}CReport{{/tr}}
    {{else}}
      {{$title}}
    {{/if}}
  </title>
</head>


<body>
<h1>
  {{if !$title}}
    {{tr}}CReport{{/tr}}
  {{else}}
    {{$title}}
  {{/if}}
</h1>

<table class="tbl">
  <tr>
    <th class="narrow">{{tr}}CItemReport-severity{{/tr}}</th>
    <th colspan="3">{{tr}}CItemReport-data{{/tr}}</th>
  </tr>

  {{foreach from=$report->getItems() item=_item}}
    {{unique_id var=item_uniqid}}
    {{assign var=hasSubItems value=$_item->getSubItems()}}

    {{* items *}}
    {{mb_include module=eai template='report/inc_report_item_mail' item=$_item}}

    {{* sub_items *}}
    {{foreach from=$_item->getSubItems() item=subItem}}
      {{mb_include module=eai template='report/inc_report_item_mail' item=$subItem is_subitem=1}}
    {{/foreach}}
  {{/foreach}}
</table>
</body>
</html>
