{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$key}}
  {{mb_return}}
{{/if}}

{{mb_default var=spec value='bool'}}

{{mb_default var=show_label value=true}}
{{mb_default var=name       value=$key}}
{{mb_default var=label      value=false}}
{{mb_default var=title      value=false}}
{{mb_default var=onclick    value=''}}

{{mb_include module=admin template=inc_inline_pref_$spec}}