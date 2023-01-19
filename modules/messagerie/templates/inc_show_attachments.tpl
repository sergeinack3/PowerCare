{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_attachment value=""}}

<style>
  img{
    max-width: 100%;
  }
</style>

{{* Conventionnal images *}}
{{assign var=mime_image value=","|explode:"jpg,png,jpeg,gif,apng"}}
{{if in_array($_attachment->extension, $mime_image)}}
  <img title="{{$_attachment->name}}" src="data:image/{{$_attachment->subtype|strtolower}};base64,{{$_attachment->_content}}" alt=""/>
{{/if}}


{{* SVG Case *}}

{{assign var=mime_image2 value=","|explode:"svg+xml,svg"}}
{{if in_array($_attachment->extension, $mime_image2)}}
  {{$_attachment->_content|smarty:nodefaults}}
{{/if}}

{{* ARCHIVE *}}
{{assign var=mime_image3 value=","|explode:"zip,tar.gz,tar,rar,7z"}}
{{if in_array($_attachment->extension, $mime_image3)}}
  <img src="images/pictures/download.png" alt=""/>
{{/if}}


{{* DOCUMENTS *}}
{{assign var=mime_image3 value=","|explode:"pdf,doc,xml,docx,odt,odp,txt,msword"}}
{{if in_array($_attachment->extension, $mime_image3)}}
  <img src="images/pictures/download.png" alt=""/>
{{/if}}

{{assign var=mime_image3 value=","|explode:"mp3,wav,ogg"}}
{{if in_array($_attachment->extension, $mime_image3)}}
  <img src="images/pictures/download.png" alt=""/>Music
{{/if}}
