<?php

declare(strict_types=1);

use App\Models\Post;
use App\Services\PostService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Slim\Views\Twig;

return static function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => static function (ContainerInterface $c): LoggerInterface {
            /** @var array{settings: array{logger: array{name: string, path: string, level: Level}}} $settings */
            $settings = $c->get('settings');
            $loggerSettings = $settings['settings']['logger'];

            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        Redis::class => static function (ContainerInterface $c): Redis {
            /** @var array{settings: array{redis: array{host: string, port: int, password: string, database: int}}} $settings */
            $settings = $c->get('settings');
            $redisSettings = $settings['settings']['redis'];

            $redis = new Redis();
            $redis->connect($redisSettings['host'], $redisSettings['port']);

            if ($redisSettings['password'] !== '') {
                $redis->auth($redisSettings['password']);
            }

            $redis->select($redisSettings['database']);

            return $redis;
        },

        Twig::class => static function (ContainerInterface $c): Twig {
            /** @var array{settings: array{twig: array{template_path: string, twig: array<string, mixed>}}} $settings */
            $settings = $c->get('settings');
            $twigSettings = $settings['settings']['twig'];

            return Twig::create($twigSettings['template_path'], $twigSettings['twig']);
        },

        Post::class => static function (ContainerInterface $c): Post {
            /** @var Redis $redis */
            $redis = $c->get(Redis::class);

            return new Post($redis);
        },

        PostService::class => static function (ContainerInterface $c): PostService {
            /** @var Post $post */
            $post = $c->get(Post::class);

            return new PostService($post);
        },
    ]);
};
