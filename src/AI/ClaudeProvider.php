<?php

namespace Draad\ContentGenerator\AI;

class ClaudeProvider implements ProviderInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option( 'dcg_claude_api_key', '' );
    }

    public function generate( string $prompt ): string
    {
        if ( empty( $this->apiKey ) ) {
            throw new \RuntimeException( 'Claude API key is niet ingesteld.' );
        }

        $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
            'timeout' => 120,
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ],
            'body' => wp_json_encode( [
                'model'      => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 4096,
                'messages'   => [
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException( 'Claude API fout: ' . $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['content'][0]['text'] ) ) {
            throw new \RuntimeException( 'Ongeldig antwoord van Claude API.' );
        }

        return $body['content'][0]['text'];
    }
}
