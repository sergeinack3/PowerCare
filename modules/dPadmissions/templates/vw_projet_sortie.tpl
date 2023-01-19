{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions  script=admissions}}

<script>
  seeProjetSortie = function(form) {
    var url = new Url('admissions', 'vw_projet_sortie');
    url.addParam('praticien_id'   , $V(form.praticien_id));
    url.addParam('_date_min_stat' , $V(form._date_min_stat));
    url.addParam('_date_max_stat' , $V(form._date_max_stat));
    url.addParam("handicap"       , [$V(form._handicap)].flatten().join(","));
    url.addParam("aide_organisee" , [$V(form.aide_organisee)].flatten().join(","));
    url.addParam('type'           , $V(form.type));
    url.addParam('tutelle'        , $V(form.tutelle));
    {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $list_mode_sortie|@count}}
      url.addParam("mode_sortie"  , [$V(form.mode_sortie_id)].flatten().join(","));
    {{else}}
      url.addParam('mode_sortie'  , [$V(form.mode_sortie)].flatten().join(","));
    {{/if}}
    url.addParam('see_sorties', 1);
    url.requestUpdate('see_projet_sortie_view');
  };

  Main.add(function(){
    seeProjetSortie(getForm('formProjetSortie'));
    $("see_projet_sortie_view").fixedTableHeaders();
  });
</script>

{{mb_ternary var=show_aide_organisee test="dPplanningOp CSejour show_aide_organisee"|gconf value=1 other=0}}
<form name="formProjetSortie" method="get" action="?">
  {{if !$show_aide_organisee}}
    <input type="hidden" name="aide_organisee" value=""/>
  {{/if}}

  <table class="form">
    <tr>
      <th class="title" colspan="10">{{tr}}mod-dPadmissions-tab-vw_projet_sortie{{/tr}}</th>
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$filter mb_field="_date_min_stat"}}
        {{mb_field object=$filter field="_date_min_stat" form="formProjetSortie" register=true canNull="false"}}
      {{/me_form_field}}

      {{me_form_field nb_cells=2 mb_object=$filter mb_field=praticien_id }}
        <select name="praticien_id" style="max-width: 15em;">
          <option value="">&mdash; Tous les praticiens</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$filter->praticien_id}}
        </select>
      {{/me_form_field}}

      {{me_form_field nb_cells=2 mb_object=$filter mb_field=_handicap rowspan=3}}
        <select name="_handicap" size="5" multiple>
          <option value="" {{if !$handicap_select|@count}}selected="selected"{{/if}}>&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$patient_handicap->_specs.handicap->_locales key=key_handicap item=name_handicap}}
            <option value="{{$key_handicap}}" {{if in_array($key_handicap, $handicap_select)}}selected="selected"{{/if}}>{{$name_handicap}}</option>
          {{/foreach}}
        </select>
      {{/me_form_field}}
      
      {{if $show_aide_organisee}}
        {{me_form_field nb_cells=2 mb_object=$filter mb_field=aide_organisee rowspan=3}}
          <select name="aide_organisee" size="5" multiple>
            <option value="1" {{if !$aide_select|@count}}selected="selected"{{/if}}>&mdash; {{tr}}All{{/tr}}</option>
            {{foreach from=$filter->_specs.aide_organisee->_locales key=key_aide item=name_aide}}
              <option value="{{$key_aide}}" {{if in_array($key_aide, $aide_select)}}selected="selected"{{/if}}>{{$name_aide}}</option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      {{/if}}

      {{me_form_field nb_cells=2 mb_object=$filter mb_field=mode_sortie rowspan=3 }}
        {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $list_mode_sortie|@count}}
          <select name="mode_sortie_id" size="5" style="width: 16em;" multiple>
            <option value="">&mdash; {{tr}}All{{/tr}}</option>
            {{foreach from=$list_mode_sortie item=_mode}}
              <option value="{{$_mode->_id}}" {{if in_array($_mode->_id, $mode_sortie_select)}}selected{{/if}}>
                {{$_mode}}
              </option>
            {{/foreach}}
          </select>
        {{else}}
          <select name="mode_sortie" size="5" multiple>
            <option value="">&mdash; {{tr}}All{{/tr}}</option>
            {{foreach from=$filter->_specs.mode_sortie->_list item=_mode}}
              <option value="{{$_mode}}" {{if in_array($_mode, $mode_sortie_select)}}selected{{/if}}>
                {{tr}}CSejour.mode_sortie.{{$_mode}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{/if}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$filter mb_field="_date_max_stat"}}
        {{mb_field object=$filter field="_date_max_stat" form="formProjetSortie" register=true canNull="false"}}
      {{/me_form_field}}
      <th></th>
      <td><button type="button" name="filter_sejours" onclick="Admissions.selectSejours('projet');" class="search me-tertiary">{{tr}}admissions-action-Admission type{{/tr}}</button></td>
      <td colspan="6"></td>
    </tr>
    <tr {{if !"dPplanningOp CSejour show_tutelle"|gconf}}style="display: none;" {{/if}}>
      <td colspan="2"></td>
      {{me_form_field nb_cells=2 mb_object=$patient mb_field=tutelle }}
        {{mb_field object=$patient field=tutelle}}
      {{/me_form_field}}
      <td colspan="6"></td>
    </tr>
    <tr>
      <td colspan="10"></td>
    </tr>
    <tr>
      <td colspan="10" class="button">
        <button type="button" onclick="seeProjetSortie(this.form);" class="search me-primary">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="see_projet_sortie_view" class="me-align-auto me-padding-0 me-bg-white">
  {{mb_include module=admissions template=inc_vw_projet_sortie}}
</div>
