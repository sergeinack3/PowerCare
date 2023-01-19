{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-CLit" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{mb_include module=system template=inc_config_bool class=CLit     var=alt_icons_sortants}}
    {{mb_include module=system template=inc_config_str  class=CLit     var=acces_icons_sortants textarea=1}}
    {{if $conf.dPhospi.CLit.acces_icons_sortants}}
      {{assign var=count_files value='Ox\Core\CMbPath::countFiles'|static_call:$conf.dPhospi.CLit.acces_icons_sortants}}
      <tr>
        <td></td>
        <td>
          {{if $count_files == 0}}
            Aucun fichier dans le répertoire
          {{elseif $count_files <= 40}}
            {{assign var=files value='Ox\Core\CMbPath::getFiles'|static_call:$conf.dPhospi.CLit.acces_icons_sortants}}
            <table class="main">
              <tr>
                <td>
                  <table class="tbl">
                    {{if is_array($files)}}
                      <tr>
                        <th>Liste des fichiers du dossier ({{$count_files}})</th>
                      </tr>
                      {{foreach from=$files item=_file}}
                        <tr>
                          <td class="text"> {{$_file}} </td>
                        </tr>
                      {{/foreach}}
                    {{else}}
                      <tr>
                        <th>Impossible de lister les fichiers</th>
                      </tr>
                    {{/if}}
                  </table>
                </td>
              </tr>
            </table>
          {{else}}
            Le dossier contient trop de fichiers pour être listé ({{$count_files}} fichiers)
          {{/if}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="100">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>