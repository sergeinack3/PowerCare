{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <input type="text" class="{{$_prop.string}}" name="c[{{$_feature}}]" value="{{$value}}" {{if $is_inherited}}disabled{{/if}} size="{{$_prop.length}}" />
{{else}}
  {{$value}}
{{/if}}