{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPccam script=CCodageCCAM ajax=true}}
<div id="info_code">
  <form name="selectContextCCAM" method="get" onsubmit="return CCodageCCAM.refreshCodeFrom('{{$code_ccam}}', this);">
    <table class="layout main">
      {{mb_default var=no_date_found value=""}}
      {{if $no_date_found}}
        <div class="small-info">{{tr}}{{$no_date_found}}{{/tr}}</div>
      {{/if}}
      <tr>
        <th style="text-align: right; width: 50%;">
          Date d'effet
        </th>
        <td style="text-align: left; width: 50%;">
          <select name="date_version" onchange="CCodageCCAM.refreshCodeFrom('{{$code_ccam}}', this.form);">
            {{foreach from=$date_versions item=_date_version}}
              <option value="{{$_date_version}}" {{if $date_version == $_date_version || $date_demandee == $_date_version}} selected{{/if}}>
                {{$_date_version}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <th colspan="2" style="font-weight: bold; text-align: center; font-size: 1.2em;"">Contexte tarifaire</th>
      </tr>
      <tr>
        <th style="text-align: right; width: 50%;">
          <label for="situation_patient">Contexte patient</label>
        </th>
        <td style="text-align: left; width: 50%;">
          <select name="situation_patient">
            <option value="none"{{if $situation_patient == 'none'}} selected{{/if}}>Hors C2S</option>
            <option value="c2s"{{if $situation_patient == 'c2s'}} selected{{/if}}>C2S</option>
            <option value="acs"{{if $situation_patient == 'acs'}} selected{{/if}}>ACS</option>
          </select>
        </td>
      </tr>
      <tr>
        <th style="text-align: right; width: 50%;">
          <label for="speciality">Spécialité</label>
        </th>
        <td style="text-align: left; width: 50%;">
          <select name="speciality">
            {{foreach from=$specialities item=_speciality}}
              <option value="{{$_speciality->_id}}"{{if $speciality == $_speciality->_id}} selected{{/if}}>
                {{$_speciality}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <th style="text-align: right; width: 50%;">
          <label for="sector">Secteur</label>
        </th>
        <td style="text-align: left; width: 50%;">
          <select name="sector">
            <option value="1"{{if $sector == '1'}} selected{{/if}}>Secteur 1</option>
            <option value="1dp"{{if $sector == '1dp'}} selected{{/if}}>Secteur 1 DP</option>
            <option value="2"{{if $sector == '2'}} selected{{/if}}>Secteur 2</option>
            <option value="nc"{{if $sector == 'nc'}} selected{{/if}}>Non conventionné</option>
          </select>
        </td>
      </tr>
      <tr>
        <th style="text-align: right; width: 50%;">
          <label for="contract">Pratique tarifaire</label>
        </th>
        <td style="text-align: left; width: 50%;">
          <select name="contract">
            <option value="none"{{if $contract == 'none'}} selected{{/if}}>Aucune</option>
            <option value="optam"{{if $contract == 'optam'}} selected{{/if}}>OPTAM</option>
            <option value="optamco"{{if $contract == 'optamco'}} selected{{/if}}>OPTAM-CO</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button type="button" class="search" onclick="this.form.onsubmit();">Afficher le tarif</button>
        </td>
      </tr>
    </table>
  </form>
  <div>
    <h2 style="text-align: center;"><strong>{{$code_ccam}}</strong><br/>{{$code_complet->libelleLong}}</h2>
    {{mb_include module=ccam template=inc_show_code_from_date}}
  </div>
</div>

