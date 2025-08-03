<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PostService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

/**
 * Controller for handling post operations.
 */
final class PostController
{
    public function __construct(
        private readonly PostService $postService,
        private readonly Twig $twig,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Display posts list.
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $page = (int) ($queryParams['page'] ?? 1);
            $limit = (int) ($queryParams['limit'] ?? 10);

            $data = $this->postService->getAllPosts($page, $limit);

            return $this->twig->render($response, 'posts/index.html.twig', $data);
        } catch (\Throwable $e) {
            $this->logger->error('Error fetching posts', ['exception' => $e]);

            return $response->withStatus(500);
        }
    }

    /**
     * Display single post.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            /** @var string $id */
            $id = $args['id'] ?? '';

            $post = $this->postService->getPost($id);
            if ($post === null) {
                return $response->withStatus(404);
            }

            return $this->twig->render($response, 'posts/show.html.twig', ['post' => $post]);
        } catch (\Throwable $e) {
            $this->logger->error('Error fetching post', ['id' => $args['id'] ?? '', 'exception' => $e]);

            return $response->withStatus(500);
        }
    }

    /**
     * Show create post form.
     */
    public function create(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'posts/create.html.twig');
    }

    /**
     * Store new post.
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            /** @var array<string, mixed> $data */
            $data = $request->getParsedBody() ?? [];

            $post = $this->postService->createPost($data);

            return $response
                ->withHeader('Location', '/posts/' . $post['id'])
                ->withStatus(302);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Validation error creating post', ['error' => $e->getMessage()]);

            return $this->twig->render($response->withStatus(422), 'posts/create.html.twig', [
                'error' => $e->getMessage(),
                'old' => $request->getParsedBody(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Error creating post', ['exception' => $e]);

            return $response->withStatus(500);
        }
    }

    /**
     * Show edit post form.
     */
    public function edit(Request $request, Response $response, array $args): Response
    {
        try {
            /** @var string $id */
            $id = $args['id'] ?? '';

            $post = $this->postService->getPost($id);
            if ($post === null) {
                return $response->withStatus(404);
            }

            return $this->twig->render($response, 'posts/edit.html.twig', ['post' => $post]);
        } catch (\Throwable $e) {
            $this->logger->error('Error fetching post for edit', ['id' => $args['id'] ?? '', 'exception' => $e]);

            return $response->withStatus(500);
        }
    }

    /**
     * Update post.
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            /** @var string $id */
            $id = $args['id'] ?? '';
            /** @var array<string, mixed> $data */
            $data = $request->getParsedBody() ?? [];

            $post = $this->postService->updatePost($id, $data);
            if ($post === null) {
                return $response->withStatus(404);
            }

            return $response
                ->withHeader('Location', '/posts/' . $post['id'])
                ->withStatus(302);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Validation error updating post', ['id' => $args['id'] ?? '', 'error' => $e->getMessage()]);

            $post = $this->postService->getPost($args['id'] ?? '');
            if ($post === null) {
                return $response->withStatus(404);
            }

            return $this->twig->render($response->withStatus(422), 'posts/edit.html.twig', [
                'post' => $post,
                'error' => $e->getMessage(),
                'old' => $request->getParsedBody(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Error updating post', ['id' => $args['id'] ?? '', 'exception' => $e]);

            return $response->withStatus(500);
        }
    }

    /**
     * Delete post.
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            /** @var string $id */
            $id = $args['id'] ?? '';

            if (!$this->postService->deletePost($id)) {
                return $response->withStatus(404);
            }

            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        } catch (\Throwable $e) {
            $this->logger->error('Error deleting post', ['id' => $args['id'] ?? '', 'exception' => $e]);

            return $response->withStatus(500);
        }
    }
}
