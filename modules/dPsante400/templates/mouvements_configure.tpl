{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Mouvements</h2>

<script type="text/javascript">

  var Moves = {
    board: function () {
      this.url = new Url('sante400', 'mouvements_board');
      this.url.requestModal();
    },
    boardAction: function (action, type) {
      var url = new Url('sante400', 'ajax_do_moves');
      url.addParam('action', action);
      url.addParam('type', type);
      url.requestUpdate(SystemMessage.id, this.url.refreshModal.bind(this.url));
    },

    doImport: function () {
      var url = new Url('sante400', 'ajax_do_import');
      url.addElement($('ImportType'));
      url.addElement($('ImportOffset'));
      url.addElement($('ImportStep'));
      url.addElement($('ImportVerbose'));
      var onComplete = $('ImportAuto').checked ? Moves.doImport : Prototype.emptyFunction;
      url.requestUpdate('doImport', onComplete);

      var offset = parseInt($V('ImportOffset'), 10);
      var step = parseInt($V('ImportStep'), 10);
      $V('ImportOffset', offset + step);
    }
  }

</script>

<div style="margin: auto;">

  <button class="search singleclick" onclick="Moves.board();">
    Tableau de bord
  </button>

</div>

<table class="tbl">
  <tr>
    <th class="narrow">Mouvements</th>
    <th class="narrow">Action</th>
    <th>Status</th>
  </tr>

  <tr>
    <td>
      <div>
        <label for="ImportType" title="{{tr}}CMouvement400-type-desc{{/tr}}">{{tr}}CMouvement400-type{{/tr}}</label>
        <select id="ImportType" name="type">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$types item=_type}}
            <option value="{{$_type}}">{{tr}}CMouvement400-type-{{$_type}}{{/tr}}</option>
            {{foreachelse}}
            <option value="">Pas de type disponible</option>
          {{/foreach}}
        </select>
      </div>

      <div>
        <label for="ImportOffset">Offset</label>
        <input id="ImportOffset" type="text" name="offset" value="0"/>
      </div>

      <div>
        <label for="ImportStep">Step</label>
        <input id="ImportStep" type="text" name="step" value="1"/>
      </div>

      <div>
        <input id="ImportAuto" type="checkbox" name="auto" value="1"/>
        <label for="ImportAuto">Auto</label>
      </div>

      <div>
        <input id="ImportVerbose" type="checkbox" name="verbose" value="1"/>
        <label for="ImportVerbose">Verbose</label>
      </div>

    </td>
    <td>

      <button class="change singleclick" onclick="Moves.doImport()">
        {{tr}}Import{{/tr}}
      </button>
    </td>
    <td class="text" id="doImport"></td>
  </tr>

</table>