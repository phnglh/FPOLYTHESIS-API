<?php

namespace App\JsonApi\V2;

use App\JsonApi\V2\Categories\CategorySchema;
use App\JsonApi\V2\Products\ProductSchema;
use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{
    /**
     * The base URI namespace for this server.
     */
    protected string $baseUri = '/api/v2';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     */
    public function serving(): void
    {
        // no-op
    }

    /**
     * Get the server's list of schemas.
     */
    protected function allSchemas(): array
    {
        return [
            // @TODO
            CategorySchema::class,
            ProductSchema::class,
        ];
    }
}
