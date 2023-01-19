{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td>
      <h4>{{if $key|strpos:'session' !== false}}{{$key|truncate:19}}{{else}}{{$key}}{{/if}}</h4></td>
  </tr>
  <tr>
    <td>{{$value|highlight:json:null:"width:540px; overflow: auto;"}}</td>
  </tr>
  
  <tr>
    <td class="button">
      <button type="button" class="trash" data-type="{{$type}}" data-key="{{$key}}"
              onclick="CacheViewer.removeKey(this); Control.Modal.close();">
        {{tr}}Delete{{/tr}}
      </button>
    </td>
  </tr>
</table>
