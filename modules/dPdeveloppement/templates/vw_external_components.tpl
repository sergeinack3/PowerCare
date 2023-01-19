{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  installExternalComponent = function(name, type, action) {
    var url = new Url("developpement", "do_install_module", "dosql");
    url.addParam("name", name);
    url.addParam("type", type);
    url.addParam("action", action);
    url.requestUpdate(SystemMessage.id, {
      method: "post",
      onComplete: function() {
        document.location.reload();
      }
    });
  }
</script>

<table class="main tbl">
  {{foreach from=$components item=_components key=_type}}
    <tr>
      <th colspan="2">{{$_type}}</th>
    </tr>

    {{foreach from=$_components item=_component key=_name}}
      <tr>
        <td>{{$_name}}</td>
        <td>
          {{if !$_component.installed}}
            <button class="tick" onclick="installExternalComponent('{{$_name}}', '{{$_type}}', 'install')">
              {{tr}}Install{{/tr}}
            </button>
          {{else}}
            <button class="remove" onclick="installExternalComponent('{{$_name}}', '{{$_type}}', 'remove')">
              {{tr}}Remove{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>