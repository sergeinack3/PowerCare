{{*
 * @package Mediboard\Transport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=salleOp script=geste_perop ajax=true}}

<table class="tbl me-no-align me-margin-top-10">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <button class="tick me-primary" onclick="GestePerop.changeProtocoleItem();"
              title="{{tr}}CProtocoleGestePeropItem-msg-Change the category by the perop gestures of this category which will be associated with the perop gesture protocols{{/tr}}">
        {{tr}}CProtocoleGestePeropItem-action-Modify protocol item context{{/tr}}
      </button>
    </td>
    <td id="change_item"></td>
  </tr>
</table>
