{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('search-hd-etab');
    form.onsubmit();
  });

  changePage = function(page) {
    var form = getForm('search-hd-etab');
    var url = new Url('openData', 'ajax_search_hd_etab');
    url.addFormData(form);
    url.addParam('start', page);
    url.requestUpdate('result-search-etab-hd');
  }
</script>

<form name="search-hd-etab" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-search-etab-hd')">
  <input type="hidden" name="m" value="openData"/>
  <input type="hidden" name="a" value="ajax_search_hd_etab"/>

  <table class="main form">
    <tr>
      <th>{{mb_label class=CHDEtablissement field=finess}}</th>
      <td><input type="text" name="finess"/></td>

      <th>{{mb_label class=CHDEtablissement field=raison_sociale}}</th>
      <td>{{mb_field class=CHDEtablissement field=raison_sociale canNull=true}}</td>

      <th>{{mb_label class=CHDEtablissement field=champ_pmsi}}</th>
      <td>
        <select name="champ_pmsi">
          <option value="">-</option>
          {{foreach from=$champ_pmsi item=_pmsi}}
            <option value="{{$_pmsi}}">{{$_pmsi}}</option>
          {{/foreach}}
        </select>
      </td>

      <th>{{mb_label class=CHDEtablissement field=cat}}</th>
      <td>
        <select name="cat">
          <option value="">-</option>
          {{foreach from=$categories item=_cat}}
            <option value="{{$_cat}}">{{$_cat}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label class=CHDEtablissement field=taille_mco}}</th>
      <td>
        <select name="taille_mco">
          <option value="">-</option>
          {{foreach from=$taille_mco item=_mco}}
            <option value="{{$_mco}}">{{$_mco}}</option>
          {{/foreach}}
        </select>
      </td>

      <th>{{mb_label class=CHDEtablissement field=taille_m}}</th>
      <td>
        <select name="taille_m">
          {{foreach from=$taille_m item=_m}}
            <option value="{{$_m}}">{{$_m}}</option>
          {{/foreach}}
        </select>
      </td>

      <th>{{mb_label class=CHDEtablissement field=taille_c}}</th>
      <td>
        <select name="taille_c">
          {{foreach from=$taille_c item=_c}}
            <option value="{{$_c}}">{{$_c}}</option>
          {{/foreach}}
        </select>
      </td>

      <th>{{mb_label class=CHDEtablissement field=taille_o}}</th>
      <td>
        <select name="taille_o">
          {{foreach from=$taille_o item=_o}}
            <option value="{{$_o}}">{{$_o}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label class=CHDIdentite field=nb_lits_med}} (+/- 50)</th>
      <td>
        <input type="number" min="0" name="nb_lits_med" size="3"/>
      </td>

      <th>{{mb_label class=CHDIdentite field=nb_lits_chir}} (+/- 50)</th>
      <td>
        <input type="number" min="0" name="nb_lits_chir" size="3"/>
      </td>

      <th>{{mb_label class=CHDIdentite field=nb_lits_obs}} (+/- 50)</th>
      <td>
        <input type="number" min="0" name="nb_lits_obs" size="3"/>
      </td>

      <td colspan="2"></td>
    </tr>

    <tr>
      <td class="button" colspan="8">
        <button class="button search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>

  </table>
</form>

<div id="result-search-etab-hd"></div>
