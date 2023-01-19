{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  viewTransmissions = function(sejour_id, praticien_id){
    new Url('prescription', 'httpreq_vw_transmissions')
      .addParam("sejour_id", sejour_id)
      .addParam("praticien_id", document.selPraticien.praticien_id.value)
      .requestUpdate('view_transmissions');
  };

  tri_transmissions = function(order_col, order_way) {
    new Url('prescription', 'httpreq_vw_transmissions')
      .addParam('praticien_id', document.selPraticien.praticien_id.value)
      .addParam('order_col', order_col)
      .addParam('order_way', order_way)
      .requestUpdate('view_transmissions');
  };

  function markAsSelected(element) {
    $('list_patients').select('.selected').invoke('removeClassName', 'selected');
    $(element).up(1).addClassName('selected');
  }

  Main.add(function() {
    viewTransmissions();
    if ($('last_trans')){
      $('last_trans').addClassName('selected');
    }
  });
</script>

<table class="main">
  <tr>
    <td style="width: 150px;">
      <table class="form">
        <tr>
          <th class="category">Praticien</th>
        </tr>
        <tr>
          <td>
            <form name="selPraticien" action="?" method="get">
              <input type="hidden" name="m" value="{{$m}}" />
              <input type="hidden" name="tab" value="{{$tab}}" />
              <select name="praticien_id" onchange="this.form.submit();">
              {{foreach from=$praticiens item=_praticien}}
                <option {{if $praticien_id == $_praticien->_id}}selected="selected"{{/if}} value="{{$_praticien->_id}}">{{$_praticien->_view}}</option>
              {{/foreach}}
              </select>
            </form>
          </td>
        </tr>
      </table>
      <table class="tbl" id="list_patients">
        {{if $sejours|@count}}
        <tr id="last_trans">
          <td>
            <a href="#" onclick="markAsSelected(this); viewTransmissions();">Transmissions des dernières 24h</a>
          </td>
        </tr>
        <tr>
          <th>Toutes les transmissions par patients</th>
        </tr>
        {{foreach from=$sejours item=_sejour}}
          <tr>
            <td>
              <a href="#" onclick="markAsSelected(this); viewTransmissions('{{$_sejour->_id}}');">{{$_sejour->_ref_patient->_view}}</a>
            </td>
          </tr>
        {{/foreach}}
        {{/if}}
      </table>
    </td>
    <td id="view_transmissions"></td>
  </tr>
</table>