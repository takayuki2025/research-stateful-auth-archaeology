<?php

namespace App\Modules\Comment\Infrastructure;

use Illuminate\Support\ServiceProvider;
use App\Modules\Comment\Domain\Repository\CommentRepository;
use App\Modules\Comment\Infrastructure\Persistence\EloquentCommentRepository;

final class CommentServiceProvider extends ServiceProvider
{
    public function register(): void
    {


        $this->app->bind(
            CommentRepository::class,
            EloquentCommentRepository::class
        );

    }
}
