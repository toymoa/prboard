<?php

declare(strict_types=1);

use App\Models\Post;
use App\Services\PostService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Predis\Client as RedisClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
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

        RedisClient::class => static function (ContainerInterface $c): RedisClient {
            /** @var array{settings: array{redis: array{host: string, port: int, password: string, database: int}}} $settings */
            $settings = $c->get('settings');
            $redisSettings = $settings['settings']['redis'];

            return new RedisClient([
                'scheme' => 'tcp',
                'host' => $redisSettings['host'],
                'port' => $redisSettings['port'],
                'password' => $redisSettings['password'] !== '' ? $redisSettings['password'] : null,
                'database' => $redisSettings['database'],
            ]);
        },

        Twig::class => static function (ContainerInterface $c): Twig {
            /** @var array{settings: array{twig: array{template_path: string, twig: array<string, mixed>}}} $settings */
            $settings = $c->get('settings');
            $twigSettings = $settings['settings']['twig'];

            return Twig::create($twigSettings['template_path'], $twigSettings['twig']);
        },

        Post::class => static function (ContainerInterface $c): Post {
            /** @var RedisClient $redis */
            $redis = $c->get(RedisClient::class);

            return new Post($redis);
        },

        PostService::class => static function (ContainerInterface $c): PostService {
            /** @var Post $post */
            $post = $c->get(Post::class);

            return new PostService($post);
        },
    ]);
};
