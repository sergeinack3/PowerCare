{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{$table->class}} - {{$field->field}}</h2>

{{if $meta}}
  <script>
    Main.add(function() {
      Control.Tabs.create('tabs_errors_classes', false, {
        afterChange: function(container) {
          var url = new Url('dPdeveloppement', 'ajax_display_errors_details');
          url.addParam('container', container.id);
          url.addParam('field_id', {{$field->_id}});
          url.requestUpdate(container);
        }
      });
      {{foreach from=$errors key=_class item=_count}}
      Control.Tabs.setTabCount('{{$_class}}', {{$_count.count_errors}});
      {{/foreach}}
    });
  </script>

  <table class="main layout">
    <tr>
      <td>
        <ul id="tabs_errors_classes" class="control_tabs_vertical">
          {{foreach from=$errors key=_class item=_count}}
            <li><a href="#{{$_class}}">{{$_class}}</a></li>
          {{/foreach}}
        </ul>
      </td>
      <td>
        {{foreach from=$errors key=_class item=_count}}
          <div id="{{$_class}}" style="display: none;">
            {{$_class}}
          </div>
        {{/foreach}}
      </td>
    </tr>
  </table>
{{else}}
  <script>
  Main.add(function() {
    var url = new Url('dPdeveloppement', 'ajax_display_errors_details');
    url.addParam('field_id', {{$field->_id}});
    url.addParam('container', '{{$field->field}}');
    url.requestUpdate('{{$field->field}}');
  });
  </script>
  
  <div id="{{$field->field}}"></div>
{{/if}}
