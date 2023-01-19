<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Transformers;

class ErrorTransformer
{
    private $error;

    /**
     * ErrorTransformer constructor.
     *
     * @param object $error
     */
    public function __construct($error)
    {
        $this->error = $error;
    }

    /**
     * @return array
     */
    public function transform(): array
    {
        return [
            'id'           => $this->error->error_log_id,
            'error_log_id' => $this->error->error_log_id,
            'user_id'      => $this->error->user_id,
            'server_ip'    => $this->error->server_ip,
            'datetime'     => $this->error->datetime,
            'request_uid'  => $this->error->request_uid,
            'error_type'   => $this->error->error_type,
            'text'         => $this->error->text,
            'file'         => $this->error->file_name . ':' . $this->error->line_number,
        ];
    }
}
