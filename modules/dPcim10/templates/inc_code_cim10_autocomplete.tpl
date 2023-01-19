{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left;">
  {{foreach from=$codes item=_code}}
    <li>
      {{if $_code->_favoris_id}}
        <span style="float:right;">
          <i class="fas fa-star" style="height: 16px; color: goldenrod;" title="{{tr}}CCodeCIM10-msg-is_favori{{/tr}}"></i>
        </span>
      {{/if}}
      <span class="code">{{$_code->code}}</span>
      <div style="margin-left: 15px; color: #888">
        {{$_code->libelle|spancate:40}}
      </div>
    </li>
  {{/foreach}}
</ul>
