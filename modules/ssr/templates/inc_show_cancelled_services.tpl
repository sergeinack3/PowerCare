{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<input type="hidden" name="show_cancelled_services" value="{{$show_cancelled_services|ternary:1:0}}" onchange="this.form.onsubmit();">
<input type="checkbox" name="_show_cancelled_services" value="1" {{if $show_cancelled_services}} checked="checked" {{/if}} 
  onclick="$V(this.form.show_cancelled_services, $V(this)?1:0);"
>
<label for="_show_cancelled_services">
  {{tr}}ssr-_show_cancelled_services{{/tr}}
</label>
