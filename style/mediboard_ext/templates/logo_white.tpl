{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=alt   value=""}}
{{mb_default var=title value=""}}

{{assign var=logo        value="./style/mediboard_ext/images/icons/logo_white.svg"}}
{{assign var=logo_custom value="images/pictures/logo_white_custom.svg"}}

{{if !is_file($logo_custom)}}
  {{assign var=logo_custom value="images/pictures/logo_white_custom.png"}}
{{/if}}

{{if is_file($logo_custom)}}
  {{assign var=logo value=$logo_custom}}
{{else}}

{{/if}}

<img src="{{$logo}}"
     class="me-logo-white"
  {{if $alt}}alt="{{$alt}}"{{/if}}
  {{if $title}}title="{{$title}}"{{/if}} />
