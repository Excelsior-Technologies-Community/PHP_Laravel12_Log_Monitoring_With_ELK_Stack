<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

use Illuminate\Support\Facades\Notification;

use App\Notifications\ErrorLogNotification;


class ErrorAlertHandler extends AbstractProcessingHandler
{


    public function __construct()
    {

        parent::__construct(
            Level::Error
        );

    }


    protected function write(LogRecord $record): void
    {


        $data = [

            'level' =>
                $record->level->getName(),


            'message' =>
                $record->message,


            'url' =>
                request()->fullUrl(),


            'ip' =>
                request()->ip(),


            'user_id' =>
                auth()->id(),

        ];



        Notification::route(

            'mail',

            env('LOG_ALERT_EMAIL')

        )

        ->notify(

            new ErrorLogNotification($data)

        );


    }



}