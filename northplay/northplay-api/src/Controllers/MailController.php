<?php
namespace Northplay\NorthplayApi\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MailController
{

    public function __construct()
    {
        $this->mail_model = new \Northplay\NorthplayApi\Models\EmailLogModel;
    }

    public function incoming_http_endpoint(Request $request)
    {
        $mail = array(
            "from" => $request->mail_from,
            "to" => $request->to,
            "subject" => $request->subject,
            "date" => $request->date,
            "plain_body" => $request->plain_body,
            "direction" => "incoming",
            "created_at" => now(),
            "updated_at" => now(),
        );

        $this->mail_model->insert($mail);

        save_log("Incoming Mail", $mail);
        return 'ok';
    }
}