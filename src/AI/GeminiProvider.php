<?php

namespace Draad\ContentGenerator\AI;

class GeminiProvider implements ProviderInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option( 'dcg_gemini_api_key', '' );
    }

    public function generate( string $prompt ): string
    {
        if ( empty( $this->apiKey ) ) {
            throw new \RuntimeException( 'Gemini API key is niet ingesteld.' );
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->apiKey;

        $response = wp_remote_post( $url, [
            'timeout' => 120,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode( [
                'contents' => [
                    [
                        'parts' => [
                            [ 'text' => $prompt ],
                        ],
                    ],
                ],
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException( 'Gemini API fout: ' . $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['candidates'][0]['content']['parts'][0]['text'] ) ) {
            throw new \RuntimeException( 'Ongeldig antwoord van Gemini API.' );
        }

        return $body['candidates'][0]['content']['parts'][0]['text'];
    }
}
