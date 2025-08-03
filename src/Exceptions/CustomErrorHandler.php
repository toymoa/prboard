<?php

declare(strict_types=1);

namespace App\Exceptions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;
use Slim\Views\Twig;
use Throwable;

/**
 * Custom error handler for the application.
 */
final class CustomErrorHandler extends ErrorHandler
{
    public function __construct(
        private readonly Twig $twig,
        private readonly LoggerInterface $logger,
    ) {}

    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = $this->statusCode;
        $request = $this->request;

        // Log the error
        $this->logger->error('Application error', [
            'exception' => $exception,
            'status_code' => $statusCode,
            'uri' => (string) $request->getUri(),
            'method' => $request->getMethod(),
        ]);

        // Handle different types of exceptions
        if ($exception instanceof HttpNotFoundException) {
            return $this->renderErrorPage($statusCode, 'Page not found', 'The requested page could not be found.');
        }

        if ($statusCode >= 500) {
            return $this->renderErrorPage($statusCode, 'Internal Server Error', 'An unexpected error occurred.');
        }

        return $this->renderErrorPage($statusCode, 'Error', $exception->getMessage());
    }

    /**
     * Render error page.
     */
    private function renderErrorPage(int $statusCode, string $title, string $message): Response
    {
        try {
            $response = $this->responseFactory->createResponse($statusCode);

            return $this->twig->render($response, 'errors/error.html.twig', [
                'status_code' => $statusCode,
                'title' => $title,
                'message' => $message,
            ]);
        } catch (Throwable $e) {
            // Fallback to plain text response if template rendering fails
            $this->logger->error('Failed to render error template', ['exception' => $e]);
            $response = $this->responseFactory->createResponse($statusCode);
            $response->getBody()->write(sprintf(
                '<h1>%d %s</h1><p>%s</p>',
                $statusCode,
                $title,
                htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8')
            ));

            return $response->withHeader('Content-Type', 'text/html');
        }
    }
}
