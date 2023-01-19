{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    Control.Tabs.create('tabs-detail');
  });
</script>

<strong>Code {{$code->code}}</strong>

{{if $can->edit}}
  <form name="addFavoris" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
    <input type="hidden" name="m" value="dPccam" />
    <input type="hidden" name="dosql" value="storeFavoris" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="favoris_code" value="{{$code->code}}" />
    <input type="hidden" name="favoris_user" value="{{$user}}" />
    <input type="hidden" name="favoris_function" value="{{$user->function_id}}" />
    <input type="hidden" name="ajax" value="1" />
    <select name="object_class" class="{{$favoris->_props.object_class}}">
      <option value="COperation"  {{if $object_class == "COperation"}} selected="selected" {{/if}}>Intervention</option>
      <option value="CConsultation" {{if $object_class == "CConsultation"}} selected="selected" {{/if}}>Consultation</option>
      <option value="CSejour" {{if $object_class == "CSejour"}} selected="selected" {{/if}}>Séjour</option>
    </select>
    <button class="submit" type="button" onclick="$V(this.form.elements['favoris_function'], ''); this.form.onsubmit();">
      Ajouter à mes favoris
    </button>
    <button class="submit" type="button" onclick="$V(this.form.elements['favoris_user'], ''); this.form.onsubmit();">
      Ajouter au favoris du cabinet
    </button>
  </form>
{{/if}}

<ul id="tabs-detail" class="control_tabs">
  <li>
    <a href="#desc">
      Description
    </a>
  </li>
  <li>
    <a href="#hierarchie">
      Hiérarchie
    </a>
  </li>
  <li>
    <a href="#asso" {{if !$code->assos|@count}}class="empty"{{/if}}>
      Actes associés ({{$code->assos|@count}})
    </a>
  </li>
  <li>
    <a href="#incomp" {{if !$code->incomps|@count}}class="empty"{{/if}}>
      Actes incompatibles ({{$code->incomps|@count}})
    </a>
  </li>
</ul>

<div style="height: 70%;">
  <div id="desc" style="display: none; height: 110%; overflow-y: scroll;">
    <table style="text-align: left;">
      <tr>
        <td>
          <table>
            <tr>
              <td><strong>Description</strong><br />{{$code->libelleLong}}</td>
            </tr>

            {{foreach from=$code->remarques item=_rq}}
            <tr>
              <td><em>{{$_rq|nl2br}}</em></td>
            </tr>
            {{/foreach}}

            {{if $code->activites|@count}}
            <tr>
              <td><strong>Activités associées</strong></td>
            </tr>

            {{foreach from=$code->activites item=_act}}
              <tr>
                <td style="vertical-align: top; width: 100%">
                  <ul>
                    <li>
                      Activité {{$_act->numero}} <em>({{$_act->type}}) {{$_act->libelle}}</em> :
                      <ul>
                        <li>Phase(s) :
                          <ul>
                            {{foreach from=$_act->phases item=_phase}}
                            <li>
                              Phase {{$_phase->phase}} <em>({{$_phase->libelle}})</em> : {{$_phase->tarif|currency}}
                              {{if $_phase->charges}}
                                <br />Charges supplémentaires de cabinets possibles : {{$_phase->charges|currency}}
                              {{/if}}
                            </li>
                            {{/foreach}}
                          </ul>
                        </li>
                        <li>Modificateur(s) :
                          <ul>
                            {{foreach from=$_act->modificateurs item=_mod}}
                            <li>{{$_mod->code}} : {{$_mod->libelle}}</li>
                            {{foreachelse}}
                            <li class="empty">Aucun modificateur applicable à cet acte</li>
                            {{/foreach}}
                          </ul>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </td>
              </tr>
            {{/foreach}}
            {{/if}}

            {{if array_key_exists('code', $code->procedure)}}
            <tr>
              <td><strong>Procédures associées</strong></td>
            </tr>

            <tr>
              <td>
                {{if $code->procedure.code != "aucune"}}
                  <strong>
                    <a href="#1" onclick="Control.Modal.close(); showDetail('{{$code->procedure.code|trim}}')">
                {{/if}}
                  {{$code->procedure.code}}
                {{if $code->procedure.code != "aucune"}}
                    </a>
                  </strong>
                {{/if}}
                <br />
                {{$code->procedure.texte}}
              </td>
            </tr>
            {{/if}}

            {{if $code->remboursement !== null}}
            <tr>
              <td><strong>Remboursement</strong></td>
            </tr>

            <tr>
              <td>{{tr}}CDatedCodeCCAM.remboursement.{{$code->remboursement}}{{/tr}}</td>
            </tr>
            {{/if}}
          </table>
        </td>
      </tr>
    </table>
  </div>
  <div id="hierarchie" style="display: none; height: 110%; overflow-y: scroll;">
    <table class="tbl">
      <tr>
        <th class="category" colspan="2">Place dans la CCAM {{$code->place}}</th>
      </tr>

      {{foreach from=$code->chapitres item=_chap}}
        <tr id="chap{{$_chap.rang}}-trigger">
          <th style="text-align:left">{{$_chap.rang}}</th>
          <td class="text">
            <span onmouseover="ObjectTooltip.createDOM(this, 'chap{{$_chap.rang}}')">{{$_chap.nom}}</span>
            <div id="chap{{$_chap.rang}}" class="text" style="display: none; width: 500px;">
              <em>
                {{if $_chap.rq}}
                {{$_chap.rq|nl2br}}
                {{else}}
                * Pas d'informations
                {{/if}}
              </em>
            </div>
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  <div id="asso" style="display: none; height: 110%; overflow-y: scroll;">
    <table class="tbl">
      <tr>
        <th>Code</th>
        <th>Libellé</th>
      </tr>
      {{foreach from=$code->assos item=_asso}}
        <tr>
          <th><a href="#1" onclick="Control.Modal.close(); showDetail('{{$_asso.code}}')">{{$_asso.code}}</a></th>
          <td class="text">{{$_asso.texte}}</td>
        </tr>
      {{foreachelse}}
        <tr>
          <td colspan="2">
            Aucun code
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  <div id="incomp" style="display: none; height: 110%; overflow-y: scroll;">
    <table class="tbl">
      <tr>
        <th>Code</th>
        <th>Libellé</th>
      </tr>
      {{foreach from=$code->incomps item=_incomp}}
        <tr>
          <th><a href="#1" onclick="Control.Modal.close(); showDetail('{{$_incomp.code}}')">{{$_incomp.code}}</a></th>
          <td class="text">{{$_incomp.texte}}</td>
        </tr>
      {{foreachelse}}
        <tr>
          <td colspan="2">
            Aucun code
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
</div>
