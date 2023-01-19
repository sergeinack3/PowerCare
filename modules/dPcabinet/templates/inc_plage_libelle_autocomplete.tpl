{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  <li id="" data-id="0">
    <strong> &mdash; {{tr}}All{{/tr}}</strong>
  </li>
  {{foreach from=$plages item=_plage}}
    <li id="{{$_plage->libelle}}" data-id="{{$_plage->_id}}">
      <strong>{{$_plage->libelle}}</strong>
    </li>
  {{foreachelse}}
    <li>{{tr}}common-No label{{/tr}}</li>
  {{/foreach}}
</ul>
