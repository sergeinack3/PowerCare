{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=user_auth ajax=true}}

<script>
  Main.add(function() {
    Control.Tabs.create('auth-tabs', false, {afterChange: function (container){
      UserAuth.reloadAuthList(container.id, '{{$user->_id}}');
      }})
  });
</script>

<ul id="auth-tabs" class="control_tabs">
  <li>
    <a href="#tab-success">
      {{tr}}common-Success{{/tr}}
      (<span id="span-tab-success" class="auth-count-success" data-count="{{$user->_count_connections}}">
        {{$user->_count_connections|number_format:'0':',':' '}}
      </span>)
    </a>
  </li>
  <li>
    <a href="#tab-errors">
      {{tr}}common-Failure{{/tr}}
      (<span id="span-tab-errors" class="auth-count-errors" data-count="{{$user->_count_connections_errors}}">
        {{$user->_count_connections_errors|number_format:'0':',':' '}}
      </span>)
    </a>
  </li>
</ul>

<div id="tab-success" style="display: none"></div>
<div id="tab-errors" style="display: none"></div>
