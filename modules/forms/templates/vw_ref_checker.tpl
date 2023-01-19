{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changeVisibility = function (visible) {
    $$('tr.hatching').each(function (elt) {
      elt.setVisible(visible);
    });
  };

  showErrors = function (ex_class_id) {
    var url = new Url('forms', 'vwRefErrors');
    url.addParam('ex_class_id', ex_class_id);
    url.requestModal(null, null, {onClose: function () {window.location.reload();}});
  };

  delErrors = function (ex_class_id) {
    var url = new Url('forms', 'vwRefErrors');
    url.addParam('ex_class_id', ex_class_id);
    url.addParam('del', 1);
    url.requestUpdate('result-check-refs-ex-class-' + ex_class_id, {onComplete: function () {window.location.reload();}});
  }
</script>

<table class="main form">
  <tr>
    <th>Afficher les formulaires sans problèmes</th>
    <td>
      <label><input type="radio" name="change_visible" onclick="changeVisibility(true);"/> Oui</label>
      <label><input type="radio" name="change_visible" onclick="changeVisibility(false);" checked/> Non</label>
    </td>
  </tr>
</table>

<table class="main tbl">
  <tr>
    <th class="narrow">Form name</th>
    <th>Avancement</th>
    <th class="narrow">Nombre d'erreurs</th>
    <th class="narrow">Actions</th>
  </tr>

  {{foreach from=$ex_classes item=_ex_class}}
      {{assign var=key value="$prefix-$pre_tbl"|cat:$_ex_class->_id}}
      {{assign var=state value=null}}

      {{if $ex_class_check.$key == false}}
        {{assign var=state value="notStarted"}}
      {{elseif $ex_class_check.$key.ended == true}}
        {{assign var=state value="ended"}}
      {{/if}}

      <tr {{if $state == 'ended' && !$ex_class_check.$key.errors}}class="hatching" style="display: none;"{{/if}}>
        <td>{{mb_value object=$_ex_class field=name}} (ex_class_{{$_ex_class->_id}})</td>
        <td>
          {{if $state != 'notStarted'}}
            {{if $ex_class_check.$key.total > 0}}
              {{math assign=pct equation="(x/y)*100" x=$ex_class_check.$key.start y=$ex_class_check.$key.total}}
            {{elseif $ex_class_check.$key.start == 0}}
              {{assign var=pct value=100}}
            {{else}}
              {{assign var=pct value=0}}
            {{/if}}

            <div class="progressBar" style="width: 99%;"
                 title="{{$ex_class_check.$key.start|number_format:0:',':' '}} / {{$ex_class_check.$key.total|number_format:0:',':' '}}">

              <div class="bar normal" style="width: {{$pct}}%"></div>
              <div class="text">{{$pct}} %</div>
            </div>
          {{/if}}
        </td>

        <td>
          {{if $ex_class_check.$key}}
            {{$ex_class_check.$key.errors|@count}} formulaire(s) en erreur
          {{/if}}
        </td>

        <td>
          <form name="check-refs-ex-class-{{$_ex_class->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-check-refs-ex-class-{{$_ex_class->_id}}');">
            <input type="hidden" name="m" value="forms"/>
            <input type="hidden" name="dosql" value="doCheckFormIntegrity"/>
            <input type="hidden" name="ex_class_id" value="{{$_ex_class->_id}}"/>
            <input type="hidden" name="field" value="object"/>
            <input type="hidden" name="start" value="0"/>

            <button type="submit" class="change notext">Lancer la vérification</button>
            <button type="button" class="lookup notext" onclick="showErrors('{{$_ex_class->_id}}');"
              {{if !$ex_class_check.$key.errors}}disabled{{/if}}>
              Afficher les erreurs
            </button>
            <button type="button" class="trash notext" onclick="delErrors('{{$_ex_class->_id}}');"
              {{if $state == 'notStarted'}}disabled{{/if}}>
              Réinitialiser
            </button>
          </form>

          <div id="result-check-refs-ex-class-{{$_ex_class->_id}}"></div>
        </td>
      </tr>
  {{/foreach}}
</table>
