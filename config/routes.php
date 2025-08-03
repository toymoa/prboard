<?php

declare(strict_types=1);

use App\Controllers\PostController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    // 홈페이지 - 게시글 목록
    $app->get('/', [PostController::class, 'index']);

    // 게시글 관련 라우트
    $app->group('/posts', static function (RouteCollectorProxy $group): void {
        $group->get('', [PostController::class, 'index']);
        $group->get('/create', [PostController::class, 'create']);
        $group->post('', [PostController::class, 'store']);
        $group->get('/{id}', [PostController::class, 'show']);
        $group->get('/{id}/edit', [PostController::class, 'edit']);
        $group->put('/{id}', [PostController::class, 'update']);
        $group->patch('/{id}', [PostController::class, 'update']);
        $group->delete('/{id}', [PostController::class, 'destroy']);
    });
};
