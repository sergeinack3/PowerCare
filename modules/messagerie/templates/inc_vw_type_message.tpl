{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $subject|stripos:"Fwd:" !== false}}
  <span class="tag_head tag_head_fwd" title="{{tr}}CUserMail-fwd{{/tr}}">Fwd</span>
  {{assign var=subject value=$subject|ireplace:"FwD:":""|smarty:nodefaults}}
{{/if}}
{{if $subject|stripos:"Re:" !== false}}
  <span class="tag_head tag_head_re" title="{{tr}}CUserMail-responded{{/tr}}">Re</span>
  {{assign var=subject value=$subject|ireplace:'RE:':''|smarty:nodefaults}}
{{/if}}

{{$subject|smarty:nodefaults|truncate:100:"(...)"}}