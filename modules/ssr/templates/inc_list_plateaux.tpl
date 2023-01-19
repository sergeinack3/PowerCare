{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="new me-primary" onclick="PlateauTechnique.loadForm(0);">
  {{tr}}CPlateauTechnique-title-create{{/tr}}
</button>
<table class="tbl">
  <tr>
    <th>{{mb_title class=CPlateauTechnique field=nom}}</th>
    <th>{{tr}}CPlateauTechnique-back-equipements{{/tr}}</th>
    <th>{{tr}}CPlateauTechnique-back-techniciens{{/tr}}</th>
  </tr>

  {{foreach from=$plateaux item=_plateau}}
  <tr {{if $_plateau->_id == $plateau->_id}}class="selected"{{/if}}>
    <td>
      <a href="#" onclick="PlateauTechnique.loadForm({{$_plateau->_id}});">
        {{mb_value object=$_plateau field=nom}}
      </a>
    </td>
    <td style="text-align: right;">
      {{$_plateau->_count.equipements}}
    </td>
    <td style="text-align: right;">
      {{$_plateau->_count.techniciens}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}CPlateauTechnique.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>