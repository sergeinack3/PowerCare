{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ccam script=code_ccam}}

<script type="text/javascript">
function tagCallback(){
  location.reload();
}
</script>

<table class="main tbl">
  <tr>
    <td colspan="7">
      {{if $can->admin}}
        <button style="float: right;" class="tag-edit" type="button" onclick="Tag.manage('CFavoriCCAM')">
          Gérer les tags
        </button>
      {{/if}}

      <form name="selClass" action="?" method="get">
        <input type="hidden" name="m" value="ccam" />
        <input type="hidden" name="tab" value="viewFavoris" />

        {{mb_label object=$favoris field="_filter_class"}}
        {{mb_field object=$favoris field="_filter_class" emptyLabel="All" onchange="this.form.submit()"}}

        <label for="tag_id">Tag</label>
        <select name="tag_id" onchange="this.form.submit()" class="taglist">
          <option value=""> &mdash; {{tr}}All{{/tr}} </option>
          {{mb_include module=ccam template=inc_favoris_tag_select depth=0 show_empty=true}}
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <th></th>
    <th>{{mb_title class=CFavoriCCAM field=favoris_code}}</th>
    <th>Nom</th>
    <th>{{mb_title class=CFavoriCCAM field=rang}}</th>
    <th>Type</th>
    <th>Occurences</th>
    <th>Propriétaire</th>
  </tr>
  {{foreach from=$fusion item=curr_chap key=key_chap}}
  <tr>
    <th colspan="7" class="section">
      {{$curr_chap.nom}}
    </th>
  </tr>
  <tbody>
    {{foreach from=$curr_chap.codes item=curr_code key=key_code}}
      <tr>
        <td class="narrow">
          {{if $curr_code->favoris_id && $can->edit}}
            <form name="FavorisDel-{{$curr_code->favoris_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, function(){location.reload()})">
              <input type="hidden" name="m" value="ccam" />
              <input type="hidden" name="dosql" value="storeFavoris" />
              <input type="hidden" name="del" value="1" />
              <input type="hidden" name="favoris_id" value="{{$curr_code->favoris_id}}" />
              <button class="trash notext compact" type="submit">
                Retirer de mes favoris
              </button>
            </form>
          {{/if}}
        </td>

        <td style="background-color: #{{$curr_code->couleur}}; font-weight: bold;">
          <a href="#1" onclick="CodeCCAM.show('{{$curr_code->code}}', '{{$curr_code->class}}'); return false;">{{$curr_code->code}}</a>
        </td>
        <td class="text">
          {{if $curr_code->favoris_id && $can->edit}}
            <form name="favoris-tag-{{$curr_code->favoris_id}}" action="?" method="post" style="float: right;">
              {{if $curr_code->favoris_id}}
                {{mb_include module=system
                             template=inc_tag_binder_widget
                             object=$curr_code->_ref_favori
                             show_button=false
                             form_name="favoris-tag-`$curr_code->favoris_id`"
                             callback="tagCallback"}}
              {{/if}}
            </form>
          {{/if}}
          <a href="#1" onclick="CodeCCAM.show('{{$curr_code->code}}', '{{$curr_code->class}}'); return false;"> {{$curr_code->libelleLong}}</a>
        </td>
        <td style="text-align: center;">
          {{if $curr_code->favoris_id}}
            {{if $can->edit}}
              {{assign var=favoris_id value=$curr_code->favoris_id}}
              <form name="FavorisRang-{{$favoris_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
                <input type="hidden" name="@class" value="CFavoriCCAM" />
                <input type="hidden" name="favoris_id" value="{{$curr_code->favoris_id}}" />
                {{mb_field object=$curr_code->_ref_favori field=rang onchange="this.form.onsubmit()" form="FavorisRang-$favoris_id" increment=1}}
              </form>
            {{else}}
              {{mb_value object=$curr_code->_ref_favori field=rang}}
            {{/if}}
          {{/if}}
        </td>
        <td style="text-align: center;">{{tr}}CFavoriCCAM._filter_class.{{$curr_code->class}}{{/tr}}</td>
        <td style="text-align: center;">
          {{if $curr_code->occ==0}}
            Favoris
          {{else}}
            {{$curr_code->occ}} acte(s)
          {{/if}}
        </td>
        <td style="text-align: center;">
          {{if $curr_code->_ref_favori && $curr_code->_ref_favori->favoris_user}}
            Personnel
          {{else}}
            Cabinet
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="7">{{tr}}CFavoriCCAM.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
