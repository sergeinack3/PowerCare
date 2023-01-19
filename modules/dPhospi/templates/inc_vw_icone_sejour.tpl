{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=icone_nc value=false}}

{{assign var=suffixe_icons value=""}}
{{if $conf.dPhospi.CLit.alt_icons_sortants}}
  {{assign var=suffixe_icons value="2"}}
{{/if}}
{{assign var=chemin_acces value="modules/dPhospi/images/$lettre$suffixe_icons.png"}}
{{if $conf.dPhospi.CLit.acces_icons_sortants}}
  {{assign var=acces_icons_sortants value=$conf.dPhospi.CLit.acces_icons_sortants}}
  {{assign var=chemin_png value="$acces_icons_sortants/$lettre$suffixe_icons.png"}}
  {{assign var=count_files value='Ox\Core\CMbPath::countFiles'|static_call:$acces_icons_sortants}}
  {{if $count_files <= 40}}
    {{assign var=files value='Ox\Core\CMbPath::getFiles'|static_call:$acces_icons_sortants}}
    {{if in_array($chemin_png, $files)}}
      {{assign var=chemin_acces value="?m=hospi&raw=ajax_get_icone_sortie&lettre=$lettre$suffixe_icons"}}
    {{/if}}
  {{/if}}
{{/if}}
<span>
  <img src="{{$chemin_acces}}" alt="{{$lettre}}" title="{{tr}}CSejour.sortie.lettre.{{$lettre}}{{/tr}}" />
  {{if $icone_nc}}
    <br />
    <div class="icone_nc" title="{{tr}}CSejour-nuit_convenance-desc{{/tr}}">{{tr}}CSejour-nuit_convenance-court{{/tr}}</div>
  {{/if}}
</span>