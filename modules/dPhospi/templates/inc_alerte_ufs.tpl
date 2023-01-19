{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_alertes_ufs|@count}}
  {{mb_return}}
{{/if}}

<div class="small-warning text">
  <ul>
    {{foreach from=$object->_alertes_ufs item=_alerte}}
      <li>{{$_alerte}}</li>
    {{/foreach}}
  </ul>
</div>