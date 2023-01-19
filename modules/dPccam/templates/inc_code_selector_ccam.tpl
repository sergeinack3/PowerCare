{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    let tabs = Control.Tabs.create('tabs-code');
    {{if array_key_exists('chir', $users) && $curr_user->_id == $users.chir->_id && $listByProfile.chir.total > 0}}
      tabs.setActiveTab('chir_code');
    {{elseif array_key_exists('anesth', $users) && $curr_user->_id == $users.anesth->_id && $listByProfile.anesth.total > 0}}
      tabs.setActiveTab('anesth_code');
    {{elseif array_key_exists('search', $users)}}
      tabs.setActiveTab('search_code');
    {{elseif array_key_exists('user', $users) && $listByProfile.user.total > 0}}
      tabs.setActiveTab('user_code');
    {{/if}}
  });

  showDetail = function(code, object_class) {
    var url = new Url("dPccam", "viewDetailCodeCcam");
    url.addParam("codeacte", code);
    url.addParam("object_class", "{{$object_class}}");
    url.requestModal(600, 400);
  };

  addMultiples = function() {
    var div = $("code_area");
    var inputs = div.select(".multiples_codes:checked");
    if (inputs.length) {
      CCAMSelector.setMulti(inputs);
      Control.Modal.close();
    }
  }
</script>

<style type="text/css">
em {
  text-decoration: underline;
}
</style>

{{assign var=multiple_select value=$app->user_prefs.multiple_select_ccam}}
{{assign var="chap_name" value=""}}

<ul id="tabs-code" class="control_tabs">
{{foreach from=$listByProfile key=profile item=list}}
  {{assign var=user value=$users.$profile}}
  <li>
    <a href="#{{$profile}}_code" {{if !$list.list|@count}}class="empty"{{/if}}>
      {{tr}}Profile.{{$profile}}{{/tr}}
      {{if $profile != 'search'}}
        {{$user->_view}}
      {{/if}}
       ({{$list.total}})
    </a>
  </li>
{{/foreach}}
</ul>

{{foreach from=$listByProfile key=profile item=_profile}}
  {{assign var=list         value=$_profile.list}}
  {{assign var=list_favoris value=$_profile.favoris}}
  {{assign var=list_stats   value=$_profile.stats}}
  <div id="{{$profile}}_code" style="display: none; height: 110%; overflow-y: scroll;">
    <table class="tbl">
      <tr>
        <th>Code</th>
        <th>Libellé</th>
        <th>Tarifs</th>
        {{if !$tag_id}}
          <th>Occurences</th>
        {{/if}}
        <th class="narrow"></th>
      </tr>
      {{foreach from=$list item=list_by_type key=type_favoris}}
        {{foreach from=$list_by_type item=curr_code name=fusion}}
          {{if $curr_code->chap != $chap_name}}
          <tr>
            <th colspan="6" class="section">
              {{$curr_code->chap}}
            </th>
          </tr>
            {{assign var="chap_name" value=$curr_code->chap}}
          {{/if}}
          <tr>
            <td style="background-color: #{{$curr_code->couleur}}">
              <button type="button" class="search notext compact" onclick="showDetail('{{$curr_code->code}}');">Détail</button>
              {{$curr_code->code}}
            </td>
            <td class="text compact">{{$curr_code->libelleLong|emphasize:$_keywords_code}}</td>
            <td class="compact" style="cursor: pointer; text-align: right;">
              {{foreach from=$curr_code->activites item=_activite}}
                {{foreach from=$_activite->phases item=_phase}}
                   {{if $_phase->tarif}}
                   <div title="activité {{$_activite->numero}}, phase {{$_phase->phase}}">
                     {{$_phase->tarif|currency}}
                   </div>
                   {{/if}}
                {{/foreach}}
              {{/foreach}}
            </td>
            {{if !$tag_id}}
              <td>
                {{if $type_favoris == "favoris"}}
                  Favoris
                {{elseif array_key_exists($curr_code->code, $list_stats)}}
                  {{assign var=_code value=$curr_code->code}}
                  {{$list_stats.$_code.nb_acte}}
                {{/if}}
              </td>
            {{/if}}
            <td>
              {{if $multiple_select}}
                <input type="checkbox" class="multiples_codes"
                       data-code="{{$curr_code->code}}" data-libelle="{{$curr_code->libelleLong}}"
                       value="{{$curr_code->code}}" style="width: 25px;"/>
              {{else}}
                {{if $ged}}
                  <button type="button" class="tick compact"
                          data-code="{{$curr_code->code}}" data-libelle="{{$curr_code->libelleLong}}"
                          onclick="CCAMSelector.set(this); Control.Modal.close();">
                      {{tr}}common-action-Select{{/tr}}
                  </button>
                {{else}}
                  <button type="button" class="tick compact"
                          onclick="CCAMSelector.set('{{$curr_code->code}}', '{{$curr_code->_default}}'); Control.Modal.close();">
                      {{tr}}common-action-Select{{/tr}}
                  </button>
                {{/if}}
              {{/if}}
            </td>
          </tr>
        {{/foreach}}
      {{/foreach}}
      {{if !$list.favoris|@count && !$list.stats|@count}}
        <tr>
          <td class="empty" colspan="4">{{if $profile == 'search'}}Aucun code{{else}}Aucun favori / statistique{{/if}} </td>
        </tr>
      {{/if}}
    </table>
  </div>
{{/foreach}}
{{if $multiple_select}}
  <div style="text-align: center">
    <button type="button" class="tick" onclick="addMultiples()">Ajouter la sélection</button>
  </div>
{{/if}}
