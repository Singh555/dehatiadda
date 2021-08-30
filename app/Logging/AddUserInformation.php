<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AddUserInformation
 *
 * @author singh
 */
namespace App\Logging;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
class AddUserInformation {
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function __invoke(Logger $logger)
    {
        if ($this->request) {
            foreach ($logger->getHandlers() as $handler) {
                $handler->pushProcessor([$this, 'processLogRecord']);
            }
        }
    }

    public function processLogRecord(array $record): array
    {
        $record['extra'] += [
            'user ' => $this->request->user()->phone ?? 'guest',
        ];

        return $record;
    }
}
