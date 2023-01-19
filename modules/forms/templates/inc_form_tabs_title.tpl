{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !"forms"|module_active || !$form_tabs}}
  {{mb_return}}
{{/if}}

{{foreach from=$form_tabs.$position item=_event}}
  <li class="form-tab">
    <a href="#form-tab-{{$_event->_guid}}">
      {{if $_event->tab_name}}{{$_event->tab_name}}{{else}}{{$_event->_ref_ex_class->name}}{{/if}}
      <small>({{if $_event->_count_ex_links === null}}x{{else}}{{$_event->_count_ex_links}}{{/if}})</small>
    </a>
  </li>
{{/foreach}}