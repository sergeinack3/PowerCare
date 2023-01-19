<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Responses;

use Ox\Core\CApp;
use Symfony\Component\HttpFoundation\Response;

final class FileResponse extends Response
{
    public function __construct(string $file_name, string $file_content, string $content_type = 'text/plain')
    {
        if ($content_type === 'text/plain') {
            $content_type .= ';charset=' . CApp::$encoding;
        }

        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-Type' => $content_type,
            'Content-Disposition' => "inline;filename=\"$file_name\"",
            'Content-Length' => strlen($file_content) . ';',
        ];

        parent::__construct($file_content, 200, $headers);
    }
}
