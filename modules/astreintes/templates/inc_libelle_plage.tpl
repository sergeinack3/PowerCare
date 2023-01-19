{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if $astreinte}}
  <span class="me-text-align-center">
    {{if $astreinte->libelle}}
      <strong>{{$astreinte->libelle}}</strong>
    {{/if}}
    <br>
    {{if $astreinte->_ref_category}}
      <strong>{{$astreinte->_ref_category->name}}</strong>
    {{/if}}
  <br>
    {{$astreinte->_ref_user}}
  <br>
    {{$astreinte->phone_astreinte}}
</span>
{{/if}}
