{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left;">
  {{foreach from=$fields_customs item=_field}}
    <li value="{{$_field->libelle}}">{{$_field->libelle}}</li>
  {{/foreach}}
</ul>
