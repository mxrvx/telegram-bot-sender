<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Mgr;

use MXRVX\Telegram\Bot\Sender\App;
use MXRVX\Telegram\Bot\Sender\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;

class Version extends Controller
{
    public function get(): ResponseInterface
    {
        $results = [
            'current' => 'undefined',
            'available' => '',
        ];

        $autoloader = \MXRVX\Autoloader\App::getInstance($this->modx);
        if ($package = $autoloader->manager()->getPackage(App::NAMESPACE)) {
            $results['current'] = $package->version;
            $results['available'] = \implode(' / ', $package->getAvailableVersions());
        }

        return $this->success($results);
    }
}
