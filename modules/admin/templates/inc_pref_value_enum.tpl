{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=value_locale_prefix value="pref-$var-"}}
{{mb_default var=use_locale value=1}}

{{if $use_locale}}
  {{tr}}{{$value_locale_prefix}}{{$value}}{{/tr}}
{{else}}
  {{$value}}
{{/if}}
