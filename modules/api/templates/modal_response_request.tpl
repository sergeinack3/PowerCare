{{*
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(Control.Tabs.create("tabs-contenu"));
</script>


<table class="tbl">

  <tr>
    <th class="title">#{{$request->_id}}</th>
  </tr>

  <tr>
    <td>
      <ul id="tabs-contenu" class="control_tabs">
        <li><a href="#output">{{mb_title object=$request field=acquittement}}</a></li>
      </ul>

      <div id="output" style="display: none; height: 210px;" class="highlight-fill">
        {{mb_value object=$request field=acquittement}}
      </div>
    </td>
  </tr>

</table>
