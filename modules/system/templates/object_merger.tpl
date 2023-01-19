{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_logs_tab value=false}}

{{if !$dialog}}
    {{assign var=show_logs_tab value=true}}
{{/if}}

<script>
  Main.add(function () {
      {{if $result}}
        ObjectMerger.updateWarning(null, getForm('form-merge'));
      {{/if}}
      {{if $show_logs_tab}}
        Control.Tabs.create('merge-tabs', true);
      {{/if}}
  });

  function toggleColumn(className) {
    var form = getForm('form-merge');
    var inputs = form.select("tr.multiple ." + className + " input[type=radio]");
    ObjectMerger.base = form['_objects_id[0]'].value === form._base_object_id.value ? '_choix_1' : '_choix_2';
    inputs.each(function (input) {
      input.checked = true;
      input.onclick();
    });
  }
</script>

{{if $show_logs_tab}}
  <ul id="merge-tabs" class="control_tabs">
    <li><a href="#merge-form-tab">{{tr}}Merge{{/tr}}</a></li>
    <li><a href="#merge-logs-tab">{{tr}}CMergeLog|pl{{/tr}}</a></li>
  </ul>
{{/if}}

{{if $show_logs_tab}}
<div id="merge-form-tab" style="display: none;">{{/if}}
    {{if !$dialog}}
        {{mb_include module=system template=inc_form_merger}}
    {{/if}}

    {{if $result}}
        {{mb_script module=system script=object_merger}}
      <h2>Fusion de {{tr}}{{$result->_class}}{{/tr}}</h2>
      <table>
        <tr>
          <th class="me-valign-middle">Afficher les champs</th>
          <td class="me-padding-left-4">
            <label class="me-margin-right-8">
              <input type="checkbox" onclick="$$('tr.duplicate').invoke('setVisible', $V(this));"/>
              avec des valeurs identiques
              <strong>({{$counts.duplicate}} valeurs)</strong>
            </label>
            <label class="me-margin-right-8">
              <input type="checkbox" onclick="$$('tr.unique').invoke('setVisible', $V(this));"/>
              avec une valeur unique
              <em>({{$counts.unique}} valeurs)</em>
            </label>
            <label>
              <input type="checkbox" onclick="$$('tr.none').invoke('setVisible', $V(this));"/>
              sans valeurs
              <em>({{$counts.none}} valeurs)</em>
            </label>
          </td>
        </tr>
      </table>
      <form name="form-merge" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog={{$dialog}}" method="post"
            onsubmit="{{if $checkMerge}}return false;{{/if}}">
        <input type="hidden" name="m" value="system"/>
        <input type="hidden" name="dosql" value="do_object_merge"/>
          {{if $dialog}}
            <input type="hidden" name="postRedirect" value="m=system&{{$actionType}}=object_merger&dialog={{$dialog}}"/>
          {{/if}}
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="fast" value="0"/>
          {{foreach from=$objects item=object name=object}}
            <input type="hidden" name="_merging[{{$object->_id}}]" value="{{$object->_id}}"/>
            <input type="hidden" name="_objects_id[{{$smarty.foreach.object.index}}]" value="{{$object->_id}}"/>
          {{/foreach}}
        <input type="hidden" name="_objects_class" value="{{$result->_class}}"/>

          {{math equation="100/(count+1)" count=$objects|@count assign=width}}
        <table class="form merger">
          <tr>
            <th class="category narrow"></th>
            <th class="category" style="width: {{$width}}%;">Résultat</th>

              {{foreach from=$objects item=object name=object}}
                <th class="category" style="width: {{$width}}%;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')">
          {{tr}}{{$object->_class}}{{/tr}}
            {{$smarty.foreach.object.iteration}}
          <br/>
          {{$object}}
        </span>

                  <br/>
                  <label style="font-weight: normal;">
                    <input type="radio" name="_base_object_id" value="{{$object->_id}}"
                           {{if $object->_selected}}checked{{/if}}
                           onclick="toggleColumn('{{$object->_guid}}'); ObjectMerger.updateWarning(this)"/>
                    Utiliser comme base [#{{$object->_id}}]
                      {{if $object|instanceof:'Ox\Mediboard\Patients\CPatient'}}
                        [IPP:{{if $object->_IPP}} {{$object->_IPP}} {{else}} - {{/if}}]
                      {{/if}}
                  </label>
                </th>
              {{/foreach}}
          </tr>

            {{foreach from=$result->_specs item=spec name=spec}}
                {{if $spec->fieldName != $result->_spec->key && ($spec->fieldName|substr:0:1 != '_' || $spec->reported) && !$spec->derived}}
                    {{mb_include module=system template=inc_merge_field field=$spec->fieldName}}
                {{/if}}
            {{/foreach}}

          <tr>
            <td colspan="100" class="button">
              <script>var objects_id = {{$objects_id|@json}};</script>
              <button type="button" class="search" onclick="MbObject.viewBackRefs('{{$result->_class}}', objects_id);">
                  {{tr}}CMbObject-merge-moreinfo{{/tr}}
              </button>
            </td>
          </tr>

          <tr>
            <td colspan="100" class="text">
                {{if !$dialog}}
                  <div class="big-warning">
                    Vous êtes sur le point d'effectuer une fusion d'objets.
                    <br/>
                    <strong>Cette opération est irréversible, il est donc impératif d'utiliser cette fonction avec une
                      extrême prudence
                      !</strong>
                    <br/>

                    La fusion d'objets se déroule en trois phases :
                    <ol>
                      <li>Modification d'un des deux objets avec les propriétés choisies ci-dessus</li>
                      <li>Transfert des relations depuis l'autre objet</li>
                      <li>Suppression de l'autre objet</li>
                    </ol>
                  </div>
                {{/if}}

              <div id="confirm-0" style="display: none; text-align: left;">
                Vous êtes sur le point d'effectuer une <strong>fusion standard</strong>.
                <br/>Ce processus :
                <ul>
                  <li>effectue des vérifications d'intégrité, au risque d'échouer dans certaines circonstances</li>
                  <li>journalise tous les transferts d'objet</li>
                  <li>peut être lent, si le nombre d'objet liés est important</li>
                </ul>
                <br/>Voulez-vous <strong>confirmer cette action</strong> ?
              </div>

              <div id="confirm-1" style="display: none; text-align: left;">
                Vous êtes sur le point d'effectuer une <strong>fusion de masse</strong>.
                <br/>Ce processus :
                <ul>
                  <li>n'effectue aucune vérification d'intégrité</li>
                  <li>ne journalise que la création du nouvel objet et l'opération de fusion</li>
                  <li>est très rapide</li>
                </ul>
                <br/>Voulez-vous <strong>confirmer cette action</strong> ?
              </div>
            </td>
          </tr>

          <tr>
            <td colspan="100" class="button">
                {{if $checkMerge}}
                  <div class="big-error">
                    <p>La fusion de ces deux objets <strong>n'est pas permise</strong> à cause des problèmes suivants :
                    </p>
                    <ul>
                      <li>{{$checkMerge}}</li>
                    </ul>
                    <p>Corriger ces problèmes avant la fusion.</p>
                  </div>
                {{else}}

                    {{if !$merge_type || $merge_type == "check"}}
                      <button type="button" class="merge" onclick="return ObjectMerger.confirm('0')"
                              {{if $mode == 'fast'}}disabled{{/if}}>
                          {{tr}}Merge{{/tr}}
                      </button>
                    {{/if}}
                    {{if (!$merge_type || $merge_type == "fast") && $user->isAdmin()}}
                      <button type="button" class="merge" onclick="return ObjectMerger.confirm('1');"
                              {{if $mode == 'check'}}disabled{{/if}}>
                          {{tr}}Merge{{/tr}} {{tr}}massively{{/tr}}
                      </button>
                    {{/if}}

                {{/if}}


              <div id="warningList" class="big-warning">
                  {{tr}}CExClassMessage.type.warning{{/tr}} :
                <div id="frontWarnings">
                  <ul>
                  </ul>
                </div>
                <div id="backWarnings">
                  <ul>
                      {{if $warnings}}
                          {{foreach from=$warnings item=_warning}}
                            <li>{{$_warning}}</li>
                          {{/foreach}}
                      {{/if}}
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </table>
      </form>
    {{else}}
      <script>
        Main.add(function () {
          if (window.opener && window.opener.onMergeComplete) {
            window.opener.onMergeComplete();
            if (!$("systemMsg").down(".error, .warning")) {
              (function () {
                window.close();
              }).delay(1);
            }
          }
        });
      </script>
      <div class="small-info">
        Veuillez choisir des objets existants à fusionner.
      </div>
    {{/if}}
    {{if $show_logs_tab}}</div>{{/if}}

{{if $show_logs_tab}}
  <div id="merge-logs-tab" style="display: none;">
      {{mb_include module=system template=inc_form_merge_logs}}
  </div>
{{/if}}
