{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{if $contexts|@count}}
    <li>
      <span style="font-size: 1.1em; font-weight: bold; text-align: center;">UFs contextuelles</span>
    </li>
    {{foreach from=$contexts key=_context item=_ufs}}
      {{if $_ufs|@count}}
        <li>
          <span style="font-size: 1.1em; text-align: center; margin-left: 5px;">
            {{tr}}{{if $_context == 'CMediusers'}}CSejour-praticien_id{{else}}{{$_context}}{{/if}}{{/tr}}
          </span>
        </li>
        {{foreach from=$_ufs item=_uf}}
          <li data-id="{{$_uf->_id}}" data-view="{{$_uf->libelle}}" style="margin-left: 10px;">
            {{mb_value object=$_uf field=libelle}}
            <span style="font-size: 0.8em; color: #666">{{mb_value object=$_uf field=code}}</span>
          </li>
        {{/foreach}}
      {{/if}}
    {{/foreach}}
    <li>
      <span style="font-size: 1.1em; font-weight: bold; text-align: center;">Recherche</span>
    </li>
  {{/if}}

  {{foreach from=$ufs item=_uf}}
    <li data-id="{{$_uf->_id}}" data-view="{{$_uf->libelle}}"{{if $contexts|@count}} style="margin-left: 5px;"{{/if}}>
      {{mb_value object=$_uf field=libelle}}
      <span style="font-size: 0.8em; color: #666">{{mb_value object=$_uf field=code}}</span>
    </li>
  {{/foreach}}
</ul>