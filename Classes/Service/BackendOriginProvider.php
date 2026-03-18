<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Service;

use Psr\Http\Message\ServerRequestInterface;

use function hash_equals;
use function hash_hmac;

final class BackendOriginProvider
{
    private string $backendOrigin = '';

    public function resolve(ServerRequestInterface $request): string
    {
        if ($this->backendOrigin !== '') {
            return $this->backendOrigin;
        }

        $origin = (string) ($request->getQueryParams()['backendOrigin'] ?? '');
        $hmac = (string) ($request->getQueryParams()['backendOriginHmac'] ?? '');

        $expectedHmac = hash_hmac('sha256', $origin, (string) $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
        if ($origin !== '' && $hmac !== '' && hash_equals($expectedHmac, $hmac)) {
            $this->backendOrigin = $origin;
        }

        return $this->backendOrigin;
    }

    public function get(): string
    {
        return $this->backendOrigin;
    }
}
