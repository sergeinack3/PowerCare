{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  /* Après avoir réordonné, rafraîchir si nécessaire
    le rank dans le formulaire d'édition d'item */
  Main.add(function () {
    {{if $item->_id}}
      Prestation.getRankPrestationItem('{{$item->_id}}', '{{$item->rank}}');
    {{/if}}
  });
</script>
{{if $prestation|instanceof:'Ox\Mediboard\Hospi\CPrestationJournaliere'}}
  {{assign var=is_journaliere value=1}}
{{else}}
  {{assign var=is_journaliere value=0}}
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="4" class="title">{{tr}}CItemPrestation.all{{/tr}}</th>
  </tr>
  <tr>
    {{if $is_journaliere}}
      <th class="category narrow" colspan="2">{{mb_label class=CItemPrestation field=rank}}</th>
    {{/if}}
    <th class="category">{{mb_label class=CItemPrestation field=nom}}</th>
    {{if $is_journaliere}}
      <th class="category">{{tr}}CItemPrestation-back-sous_items{{/tr}}</th>
    {{/if}}
  </tr>
  {{foreach from=$items item=_item}}
    <tr id="item_{{$_item->_id}}"
        class="item {{if $_item->_id == $item_id}}selected{{/if}} {{if !$_item->actif}}hatching opacity-60{{/if}}">
      {{if $is_journaliere}}
        <td>
          <form name="reorderItemPrestation{{$_item->_id}}" method="post">
            <input type="hidden" name="m" value="hospi" />
            <input type="hidden" name="dosql" value="do_reorder_item" />
            <input type="hidden" name="item_id_move" value="{{$_item->_id}}" />
            <input type="hidden" name="direction" value="" />
            <img src="./images/icons/updown.gif" usemap="#map-{{$_item->_id}}" />
            <map name="map-{{$_item->_id}}">
              <area coords="0,0,10,7" href="#1"
                    onclick="Prestation.reorderItem('{{$_item->_id}}', 'up', '{{$prestation->_class}}', '{{$prestation->_id}}');
                      Prestation.getRankPrestationItem('{{$_item->_id}}', '{{$_item->rank}}', 'up');" />
              <area coords="0,8,10,14" href="#1"
                    onclick="Prestation.reorderItem('{{$_item->_id}}', 'down', '{{$prestation->_class}}', '{{$prestation->_id}}');
                      Prestation.getRankPrestationItem('{{$_item->_id}}', '{{$_item->rank}}', 'down');" />
            </map>
          </form>
        </td>
        <td>
          <div class="rank">{{$_item->rank}}</div>
        </td>
      {{/if}}
      <td>
        <a href="#1" onclick="Prestation.updateSelected('{{$_item->_id}}', 'item'); Prestation.editItem('{{$_item->_id}}')"
           class="mediuser"
           style="border-left-color: #{{$_item->color}}">
          {{mb_value object=$_item field=nom}}
        </a>
      </td>
      {{if $is_journaliere}}
        <td style="vertical-align: top;">
          <button type="button" class="add notext" style="float: right;" onclick="Prestation.editSousItem('', '{{$_item->_id}}')">
            {{tr}}Add{{/tr}}
          </button>

          {{foreach from=$_item->_refs_sous_items item=_sous_item}}
            <div {{if !$_sous_item->actif}}class="hatching opacity-60"{{/if}}>
              <button class="remove notext"
                      onclick="Prestation.delSousItem('{{$_sous_item->_id}}', '{{$_item->object_class}}', '{{$_item->object_id}}', '{{$_item->_id}}')">
                {{tr}}Remove{{/tr}}
              </button>
              <a style="display: inline-block;" href="#1" onclick="Prestation.editSousItem('{{$_sous_item->_id}}')">
                {{$_sous_item}} ({{$_sous_item->niveau}})
              </a>
            </div>
          {{/foreach}}
        </td>
      {{/if}}
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}CItemPrestation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
