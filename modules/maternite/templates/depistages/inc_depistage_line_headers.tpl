{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td style="width: 15em;"></td>
  {{foreach from=$grossesse->_back.depistages item=depistage}}
    <th style="width: 10em;">
      <button type="button" class="edit notext not-printable me-tertiary" style="float: right;"
              onclick="DossierMater.addDepistage('{{$depistage->_id}}', '{{$grossesse->_id}}');">
        {{tr}}Edit{{/tr}}
      </button>
      {{mb_value object=$depistage field=date}}
      <br />
      {{mb_value object=$depistage field=_sa}} SA
    </th>
  {{/foreach}}
  <td></td>
</tr>