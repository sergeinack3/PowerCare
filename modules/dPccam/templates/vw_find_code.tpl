{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="findCode" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="dialog" value="{{$dialog}}" />

  <table class="form">
    <tr>
      <th class="category" colspan="6">Critères de recherche</th>
    </tr>

    <tr>
      <th><label for="code" title="Code CCAM partiel ou complet">Code Partiel</label></th>
      <td><input tabindex="1" type="text" name="code" value="{{$code|stripslashes}}" maxlength="7" /></td>
      <th><label for="chap1" title="Premier niveau d'arborescence">1er niveau</label></th>
      <td>
        <select tabindex="6" name="chap1" onchange="this.form.submit()" style="width: 20em;">
          <option value="">&mdash; Choisir le 1er niveau de chapitre</option>
          {{foreach from=$listChap1 item=curr_chap key=key_chap}}
          <option value="{{$key_chap}}" {{if $key_chap == $chap1}}selected="selected"{{/if}}>
            {{$curr_chap.rank}} - {{$curr_chap.text}}
          </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="clefs" title="mots clés séparés par des espaces">Mots clefs</label></th>
      <td><input tabindex="2" type="text" name="clefs" value="{{$clefs|stripslashes}}" /></td>
      <th><label for="chap2" title="Deuxième niveau d'arborescence">2ème niveau</label></th>
      <td>
        <select tabindex="7" name="chap2" onchange="this.form.submit()"  style="width: 20em;">
          <option value="">&mdash; Choisir le 2ème niveau de chapitre</option>
          {{foreach from=$listChap2 item=curr_chap key=key_chap}}
          <option value="{{$key_chap}}" {{if $key_chap == $chap2}}selected="selected"{{/if}}>
            {{$curr_chap.rank}} - {{$curr_chap.text}}
          </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="selacces" title="Voie d'accès concerné par le code CCAM">Voie d'accès</label></th>
      <td>
        <select tabindex="3" name="selacces" onchange="this.form.submit()" style="width: 15em;">
          <option value="">&mdash; Choisir une voie d'accès</option>
          {{foreach from=$acces item=curr_acces}}
          <option value="{{$curr_acces.code}}" {{if $curr_acces.code == $selacces}}selected="selected"{{/if}}>
            {{$curr_acces.text}}
          </option>
          {{/foreach}}
        </select>
      </td>
      <th><label for="chap3" title="Troisème niveau d'arborescence">3ème niveau</label></th>
      <td>
        <select tabindex="8" name="chap3" onchange="this.form.submit()" style="width: 20em;">
          <option value="">&mdash; Choisir le 3ème niveau de chapitre</option>
          {{foreach from=$listChap3 item=curr_chap key=key_chap}}
          <option value="{{$key_chap}}" {{if $key_chap == $chap3}}selected="selected"{{/if}}>
            {{$curr_chap.rank}} - {{$curr_chap.text}}
          </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="seltopo1" title="Appareil concerné par le code CCAM">Appareil</label></th>
      <td>
        <select tabindex="4" name="seltopo1" onchange="this.form.submit()" style="width: 15em;">
          <option value="">&mdash; Choisir un appareil</option>
          {{foreach from=$topo1 item=curr_topo1}}
          <option value="{{$curr_topo1.code}}" {{if $curr_topo1.code == $seltopo1}}selected="selected"{{/if}}>
            {{$curr_topo1.text}}
          </option>
          {{/foreach}}
        </select>
      </td>
      <th><label for="chap3" title="Troisème niveau d'arborescence">4ème niveau</label></th>
      <td>
        <select tabindex="9" name="chap4" onchange="this.form.submit()" style="width: 20em;">
          <option value="">&mdash; Choisir le 4ème niveau de chapitre</option>
          {{foreach from=$listChap4 item=curr_chap key=key_chap}}
          <option value="{{$key_chap}}" {{if $key_chap == $chap4}}selected="selected"{{/if}}>
            {{$curr_chap.rank}} - {{$curr_chap.text}}
          </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th><label for="seltopo2" title="Système concerné par le code CCAM">Système</label></th>
      <td>
        <select tabindex="5" name="seltopo2" onchange="this.form.submit()" style="width: 15em;">
          <option value="">&mdash; Choisir un système</option>
          {{foreach from=$topo2 item=curr_topo2}}
          <option value="{{$curr_topo2.code}}" {{if $curr_topo2.code == $seltopo2}}selected="selected"{{/if}}>{{$curr_topo2.text}}</option>
          {{/foreach}}
        </select>
      </td>
      <td class="button" colspan="2">
        <button class="search" tabindex="7" type="submit">Rechercher</button>
      </td>
    </tr>

  </table>
</form>

<table class="findCode">
  <tr>
    <th colspan="4">
      {{if $numcodes == 100}}
      Plus de {{$numcodes}} résultats trouvés, seuls les 100 premiers sont affichés :
      {{else}}
      {{$numcodes}} résultats trouvés :
      {{/if}}
    </th>
  </tr>

  {{foreach from=$codes item=curr_code key=curr_key}}
  {{if $curr_key is div by 4}}
  <tr>
  {{/if}}
    <td>
      <span class="compact" style="float: right;">
        {{tr}}CDatedCodeCCAM.type.{{$curr_code->type}}{{/tr}}</span>
      <strong>
        <a href="?m={{$m}}&dialog={{$dialog}}&{{$actionType}}=viewCcamCode&_codes_ccam={{$curr_code->code}}&object_class={{$object_class}}">
          {{$curr_code->code}}
        </a>
      </strong>
      <br />
      {{$curr_code->libelleLong}}
    </td>
  {{if ($curr_key+1) is div by 4 or ($curr_key+1) == $codes|@count}}
  </tr>
  {{/if}}
  {{/foreach}}
</table>
