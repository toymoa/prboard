<?php

declare(strict_types=1);

namespace App\Models;

use Predis\Client as RedisClient;

/**
 * Post model for managing bulletin board posts in Redis.
 */
final class Post
{
    private const KEY_PREFIX = 'post:';
    private const LIST_KEY = 'posts';

    public function __construct(
        private readonly RedisClient $redis,
    ) {}

    /**
     * Create a new post.
     *
     * @param array{title: string, content: string, author?: string} $data
     *
     * @throws \InvalidArgumentException
     */
    public function create(array $data): string
    {
        $id = $this->generateId();
        $post = [
            'id' => $id,
            'title' => $data['title'],
            'content' => $data['content'],
            'author' => $data['author'] ?? 'Anonymous',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // 게시글 데이터 저장
        $this->redis->hmset(self::KEY_PREFIX . $id, $post);

        // 게시글 목록에 추가 (최신순)
        $this->redis->lpush(self::LIST_KEY, $id);

        return $id;
    }

    /**
     * Find a post by ID.
     *
     * @return array{id: string, title: string, content: string, author: string, created_at: string, updated_at: string}|null
     */
    public function findById(string $id): ?array
    {
        /** @var array<string, mixed> $post */
        $post = $this->redis->hgetall(self::KEY_PREFIX . $id);

        if (empty($post)) {
            return null;
        }

        return [
            'id' => (string) $post['id'],
            'title' => (string) $post['title'],
            'content' => (string) $post['content'],
            'author' => (string) $post['author'],
            'created_at' => (string) $post['created_at'],
            'updated_at' => (string) $post['updated_at'],
        ];
    }

    /**
     * Find all posts with pagination.
     *
     * @return array<int, array{id: string, title: string, content: string, author: string, created_at: string, updated_at: string}>
     */
    public function findAll(int $page = 1, int $limit = 10): array
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        $offset = ($page - 1) * $limit;
        /** @var list<string> $postIds */
        $postIds = $this->redis->lrange(self::LIST_KEY, $offset, $offset + $limit - 1);

        $posts = [];
        foreach ($postIds as $id) {
            $post = $this->findById($id);
            if ($post !== null) {
                $posts[] = $post;
            }
        }

        return $posts;
    }

    /**
     * Update a post.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): bool
    {
        $post = $this->findById($id);
        if ($post === null) {
            return false;
        }

        $updatedPost = array_merge($post, array_filter($data, static fn($value): bool => $value !== null && $value !== ''), [
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redis->hmset(self::KEY_PREFIX . $id, $updatedPost);

        return true;
    }

    /**
     * Delete a post.
     */
    public function delete(string $id): bool
    {
        $exists = $this->redis->exists(self::KEY_PREFIX . $id);
        if ($exists === 0) {
            return false;
        }

        // 게시글 데이터 삭제
        $this->redis->del(self::KEY_PREFIX . $id);

        // 게시글 목록에서 제거
        $this->redis->lrem(self::LIST_KEY, 0, $id);

        return true;
    }

    /**
     * Get total number of posts.
     */
    public function getTotal(): int
    {
        return $this->redis->llen(self::LIST_KEY);
    }

    /**
     * Generate unique post ID.
     */
    private function generateId(): string
    {
        return uniqid('post_', true);
    }
}
