<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;

/**
 * Service layer for post operations.
 */
final class PostService
{
    public function __construct(
        private readonly Post $postModel,
    ) {}

    /**
     * Create a new post.
     *
     * @param array{title: string, content: string, author?: string} $data
     *
     * @return array{id: string, title: string, content: string, author: string, created_at: string, updated_at: string}
     *
     * @throws \InvalidArgumentException
     */
    public function createPost(array $data): array
    {
        // 데이터 검증
        $validatedData = $this->validatePostData($data);

        $id = $this->postModel->create($validatedData);

        $post = $this->postModel->findById($id);
        if ($post === null) {
            throw new \RuntimeException('Failed to create post');
        }

        return $post;
    }

    /**
     * Get a post by ID.
     *
     * @return array{id: string, title: string, content: string, author: string, created_at: string, updated_at: string}|null
     */
    public function getPost(string $id): ?array
    {
        return $this->postModel->findById($id);
    }

    /**
     * Get all posts with pagination.
     *
     * @return array{
     *     posts: array<int, array{id: string, title: string, content: string, author: string, created_at: string, updated_at: string}>,
     *     pagination: array{page: int, limit: int, total: int, pages: int}
     * }
     */
    public function getAllPosts(int $page = 1, int $limit = 10): array
    {
        $posts = $this->postModel->findAll($page, $limit);
        $total = $this->postModel->getTotal();

        return [
            'posts' => $posts,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / $limit),
            ],
        ];
    }

    /**
     * Update a post.
     *
     * @param array<string, mixed> $data
     *
     * @return array{id: string, title: string, content: string, author: string, created_at: string, updated_at: string}|null
     *
     * @throws \InvalidArgumentException
     */
    public function updatePost(string $id, array $data): ?array
    {
        $validatedData = $this->validatePostData($data, false);

        if (!$this->postModel->update($id, $validatedData)) {
            return null;
        }

        return $this->postModel->findById($id);
    }

    /**
     * Delete a post.
     */
    public function deletePost(string $id): bool
    {
        return $this->postModel->delete($id);
    }

    /**
     * Validate post data.
     *
     * @param array<string, mixed> $data
     *
     * @return array{title: string, content: string, author?: string}
     *
     * @throws \InvalidArgumentException
     */
    private function validatePostData(array $data, bool $required = true): array
    {
        $validated = [];

        if (isset($data['title'])) {
            $title = trim((string) $data['title']);
            if ($title === '' && $required) {
                throw new \InvalidArgumentException('Title is required');
            }

            if (mb_strlen($title) > 200) {
                throw new \InvalidArgumentException('Title is too long (max 200 characters)');
            }

            if ($title !== '') {
                $validated['title'] = $title;
            }
        } elseif ($required) {
            throw new \InvalidArgumentException('Title is required');
        }

        if (isset($data['content'])) {
            $content = trim((string) $data['content']);
            if ($content === '' && $required) {
                throw new \InvalidArgumentException('Content is required');
            }

            if ($content !== '') {
                $validated['content'] = $content;
            }
        } elseif ($required) {
            throw new \InvalidArgumentException('Content is required');
        }

        if (isset($data['author'])) {
            $author = trim((string) $data['author']);
            if (mb_strlen($author) > 50) {
                throw new \InvalidArgumentException('Author name is too long (max 50 characters)');
            }

            if ($author !== '') {
                $validated['author'] = $author;
            }
        }

        // Ensure required fields are present when required
        if ($required) {
            if (!isset($validated['title'])) {
                throw new \InvalidArgumentException('Title is required');
            }
            if (!isset($validated['content'])) {
                throw new \InvalidArgumentException('Content is required');
            }
        }

        /** @var array{title: string, content: string, author?: string} */
        return $validated;
    }
}
