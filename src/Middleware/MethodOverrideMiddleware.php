<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware to handle HTTP method override.
 */
final class MethodOverrideMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $parsedBody = $request->getParsedBody();

        if (
            $request->getMethod() === 'POST' &&
            is_array($parsedBody) &&
            isset($parsedBody['_METHOD']) &&
            is_string($parsedBody['_METHOD'])
        ) {
            $method = strtoupper($parsedBody['_METHOD']);
            if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
                $request = $request->withMethod($method);
            }
        }

        return $handler->handle($request);
    }
}
