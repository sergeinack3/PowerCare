{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=error_class value='warning'}}
{{if !$gd_exists && (!$imagick_exists || !array_key_exists(0, $version_imagemagick))}}
  {{assign var=error_class value='error'}}
{{/if}}

<table class="main tbl">
  <tr>
    <th colspan="3">
      GD ou ImageMagick + Imagick est n�cessaire
    </th>
    <th rowspan="2">Biblioth�que Imagine</th>
  </tr>

  <tr>
    <th>Extension php GD</th>
    <th>Biblioth�que ImageMagick</th>
    <th>Extension php Imagick</th>
  </tr>

  <tr>
    {{if $gd_exists}}
      <td class="ok" align="center">Extension pr�sente et charg�e</td>
    {{else}}
      <td class="{{$error_class}}" align="center">Extension absente</td>
    {{/if}}

    {{if array_key_exists(0, $version_imagemagick)}}
      <td class="ok" align="center">{{$version_imagemagick.0}}</td>
    {{else}}
      <td class="{{$error_class}}" align="center">Biblioth�que absente</td>
    {{/if}}

    {{if $imagick_exists}}
      <td class="ok" align="center">Extension pr�sente et charg�e</td>
    {{else}}
      <td class="{{$error_class}}" align="center">Extension absente</td>
    {{/if}}

    {{if $imagine}}
      <td class="ok" align="center">Biblioth�que charg�e</td>
    {{else}}
      <td class="error" align="center">Biblioth�que absente</td>
    {{/if}}
  </tr>
</table>