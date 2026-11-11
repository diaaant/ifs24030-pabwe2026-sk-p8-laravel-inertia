<?php

namespace Tests\Feature;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Repositories\PostRepository;
use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_contracts_are_bound_to_implementations(): void
    {
        $this->assertInstanceOf(PostRepository::class, app(PostRepositoryInterface::class));
        $this->assertInstanceOf(OpenAiPostGeneratorService::class, app(AiClientInterface::class));
    }

    public function test_force_https_option_is_applied(): void
    {
        config(['app.force_https' => true]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/'));
    }
}
