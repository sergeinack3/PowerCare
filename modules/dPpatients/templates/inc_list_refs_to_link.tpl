{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  #list_Relations li {
    list-style: none;
  }
</style>

<script>
  function checkGuess() {
    var guesses = [[], [], [], []];

    $$("#list_Relations input[type=radio]").each(function (elt) {
      var guess = elt.get("guess");
      if (guess) {
        guesses[guess].push(elt);
      }
    });
    return guesses;
  }

  Main.add(function () {
    var tab = checkGuess();
    File_Attach.guessElement(tab);
  });
</script>


<ul style="text-align: left;" id="list_Relations">
  <li>
    <input type="radio" name="object" data-class="{{$patient->_class}}" data-id="{{$patient->_id}}" data-guid="{{$patient->_guid}}"
           onclick="File_Attach.setObject('{{$patient->_class}}','{{$patient->_id}}', this);"
           {{if $patient->_guid == $object_guid}}checked="checked"{{/if}} {{if $readonly}}disabled{{/if}} />
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">{{$patient}}(Dossier Patient)</strong> ({{mb_value object=$patient field=naissance}}
    <ul id="listCodables">
      <li class="title"><strong>Sejours ({{$patient->_ref_sejours|@count}}) </strong></li>
      <!-- SEJOURS -->
      {{foreach from=$patient->_ref_sejours item=_sejour}}
        <li style="margin-left:10px;" {{if $_sejour->_guess_status == 0}}class="empty color_warning"{{/if}}>
          <input data-guess="{{$_sejour->_guess_status}}" type="radio" name="object" data-class="{{$_sejour->_class}}"
                 data-id="{{$_sejour->_id}}" data-guid="{{$_sejour->_guid}}"
                 onclick="File_Attach.setObject('{{$_sejour->_class}}','{{$_sejour->_id}}', this);"
                 {{if $_sejour->_guid == $object_guid}}checked="checked"{{/if}} {{if $readonly}}disabled{{/if}} />
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
              {{$_sejour}} {{mb_include module=patients template=inc_nb_files_short nb_files=$_sejour->_ref_documents|@count nb_docs=$_sejour->_ref_files|@count}}
            </span>

          <!-- OP de SEJOUR -->
          <ul style="margin-left:10px">
            {{foreach from=$_sejour->_ref_operations item=_op}}
              <li {{if $_op->_guess_status == 0}}class="empty color_warning"{{/if}}>
                <input data-guess="{{$_op->_guess_status}}" type="radio" name="object"
                       onclick="File_Attach.setObject('{{$_op->_class}}','{{$_op->_id}}', this);" data-class="{{$_op->_class}}"
                       data-id="{{$_op->_id}}" data-guid="{{$_op->_guid}}" {{if $readonly}}disabled{{/if}} />
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}')">
                  Interv. le {{mb_value object=$_op field=_datetime}}
                </span>,
                , {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}
                {{if $_op->annulee}}
                  <span style="color: red;">[ANNULE]</span>
                {{/if}} {{mb_include module=patients template=inc_nb_files_short nb_files=$_op->_ref_documents|@count nb_docs=$_op->_ref_files|@count}}
              </li>
              {{foreachelse}}
              <li class="empty">{{tr}}COperation.none{{/tr}}</li>
            {{/foreach}}
          </ul>

          <!-- CONSULTS DE SEJOUR-->
          <ul style="margin-left:10px">
            {{foreach from=$_sejour->_ref_consultations item=_consult}}
              <li style="margin-left:10px;" {{if $_consult->_guess_status == 0}}class="empty color_warning"{{/if}}>
                <input data-guess="{{$_consult->_guess_status}}" type="radio" name="object"
                       onclick="File_Attach.setObject('{{$_consult->_class}}','{{$_consult->_id}}', this);"
                       {{if $_consult->_guid == $object_guid}}checked="checked"{{/if}} data-class="{{$_consult->_class}}"
                       data-id="{{$_consult->_id}}" data-guid="{{$_consult->_guid}}" {{if $readonly}}disabled{{/if}} />
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
                      Consult. le {{mb_value object=$_consult field=_datetime}}
                      </span>
                , {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
                {{if $_consult->annule}}
                  <span style="color: red;">[ANNULE]</span>
                {{/if}} {{mb_include module=patients template=inc_nb_files_short nb_files=$_consult->_ref_documents|@count nb_docs=$_consult->_ref_files|@count}}
              </li>
            {{/foreach}}
          </ul>
        </li>
      {{/foreach}}

      <!-- CONSULT -->
      <li><strong>Consultations ({{$patient->_ref_consultations|@count}})</strong>
        <ul style="margin-left: 10px;">
          {{foreach from=$patient->_ref_consultations item=_consult}}
            <li {{if $_consult->_guess_status == 0}}class="empty color_warning"{{/if}}>
              <input data-guess="{{$_consult->_guess_status}}" type="radio" name="object"
                     onclick="File_Attach.setObject('{{$_consult->_class}}','{{$_consult->_id}}', this);"
                     data-class="{{$_consult->_class}}" data-id="{{$_consult->_id}}" data-guid="{{$_consult->_guid}}"
                     {{if $_consult->_guid == $object_guid}}checked="checked"{{/if}} {{if $readonly}}disabled{{/if}} />
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
                  Consult. le  {{mb_value object=$_consult field=_datetime}}
                </span>
              , {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
              {{if $_consult->annule}}
                <span style="color: red;">[ANNULE]</span>
              {{/if}} {{mb_include module=patients template=inc_nb_files_short nb_files=$_consult->_ref_documents|@count nb_docs=$_consult->_ref_files|@count}}
            </li>
          {{/foreach}}
        </ul>
      </li>
    </ul>
  </li>
</ul>

