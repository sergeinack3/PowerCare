{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=class value='CEtabExterne'}}

{{if $type == "destination"}}
  {{assign var=class value='CSejour'}}
{{/if}}

Valeurs possibles pour {{mb_label class=CEtabExterne field=$type}}

<ul>
  {{foreach from=$trads item=_trad}}
    <li>{{$_trad}} : {{tr}}{{$class}}.{{$type}}.{{$_trad}}{{/tr}}</li>
  {{/foreach}}
</ul>