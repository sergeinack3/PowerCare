{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="4">Liste des codes NGAP disponibles pour la spécialité {{$spec->text|strtolower|ucfirst}}</th>
  </tr>
  {{foreach from=$codes key=_index item=_code}}
    {{if $_index is div by 4}}
      <tr>
    {{/if}}

    <td class="text">
      <span{{if $_code->lettre_cle}} style="font-weight: bold;"{{/if}}>
        {{$_code->code}} : {{$_code->libelle}}
      </span>
      <br>
      <span>
        Montant : {{$_code->_tarif->tarif|currency}}
        {{if $_code->_tarif->debut && $_code->_tarif->fin}}
          (du {{$_code->_tarif->debut|date_format:$conf.date}} au {{$_code->_tarif->fin|date_format:$conf.date}})
        {{elseif $_code->_tarif->debut}}
          (à partir du {{$_code->_tarif->debut|date_format:$conf.date}})
        {{elseif $_code->_tarif->fin}}
          (jusqu'au {{$_code->_tarif->fin|date_format:$conf.date}})
        {{/if}}
      </span
    </td>

    {{if ($_index+1) is div by 4 or ($_index+1) == $codes|@count}}
      </tr>
    {{/if}}
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">Aucun code NGAP disponible</td>
    </tr>
  {{/foreach}}
</table>