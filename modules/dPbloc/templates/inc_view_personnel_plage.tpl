{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>Anesthésiste</th>
    <td class="greedyPane">
      <form name="editPlage" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="bloc" />
        <input type="hidden" name="dosql" value="do_plagesop_aed" />
        <input type="hidden" name="del" value="0" />
        {{mb_key object=$plage}}
        <input type="hidden" name="_repeat" value="1" />
        <input type="hidden" name="_type_repeat" value="simple" />
      
        <select name="anesth_id" style="width: 15em;" onchange="onSubmitFormAjax(this.form, reloadPersonnelPrevu);">
          <option value="">&mdash; Aucun anesthésiste</option>
          {{foreach from=$listAnesth item=_anesth}}
            <option value="{{$_anesth->_id}}" {{if $plage->anesth_id == $_anesth->_id}}selected{{/if}}>
              {{$_anesth->_view}}
            </option>
          {{/foreach}}
        </select>
      </form>
    </td>
    <td colspan="2"></td>
  </tr>
  <tr>
    {{mb_include module=bloc template=inc_view_personnel_type  name="IADE"                  list=$listPers.iade             type="iade"}}
    {{mb_include module=bloc template=inc_view_personnel_type  name="Sagefemme"             list=$listPers.sagefemme        type="sagefemme"}}
  </tr>
  <tr>
    {{mb_include module=bloc template=inc_view_personnel_type  name="AideOp"                list=$listPers.op               type="op"}}
    {{mb_include module=bloc template=inc_view_personnel_type  name="Manipulateur"          list=$listPers.manipulateur     type="manipulateur"}}
  </tr>
  <tr>
    {{mb_include module=bloc template=inc_view_personnel_type  name="Panseuse"              list=$listPers.op_panseuse      type="op_panseuse"}}
    {{mb_include module=bloc template=inc_view_personnel_type  name='Aux. de puériculture'  list=$listPers.aux_puericulture type="aux_puericulture"}}
  </tr>
  <tr>
    {{mb_include module=bloc template=inc_view_personnel_type  name='Instrumentiste'        list=$listPers.instrumentiste   type="instrumentiste"}}
    {{mb_include module=bloc template=inc_view_personnel_type  name='Aide soignant'         list=$listPers.aide_soignant    type="aide_soignant"}}
  </tr>
  <tr>
    {{mb_include module=bloc template=inc_view_personnel_type  name='Circulante'            list=$listPers.circulante       type="circulante"}}
    {{mb_include module=bloc template=inc_view_personnel_type  name='Brancardier'           list=$listPers.brancardier      type="brancardier"}}
    <td colspan="2"></td>
  </tr>
</table>
