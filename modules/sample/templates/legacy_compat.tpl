{{*
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <script>
    Main.add(function () {
      Control.Tabs.create('sample-legacy-tabs');
    });
  </script>

  <div>
    <ul class="control_tabs" id="sample-legacy-tabs">
      <li><a href="#sample-legacy-smarty">Smarty</a></li>
      <li><a href="#sample-legacy-vue">Vue</a></li>
    </ul>
  </div>


  <div id="sample-legacy-smarty">
    <h1>Hello smarty</h1>
    <div style="width:250px;padding: 10px;">
        {{$user}}
    </div>
    <div>
      <input type="text" id="inputSmarty">
    </div>
  </div>
  <div id="sample-legacy-vue" style="display: none">
      {{mb_entry_point entry_point=$legacy_compat}}
    <div id="sample-smarty-container"></div>
  </div>


</div>
