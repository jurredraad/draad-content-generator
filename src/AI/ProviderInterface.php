<?php

namespace Draad\ContentGenerator\AI;

interface ProviderInterface
{
    public function generate( string $prompt ): string;
}
