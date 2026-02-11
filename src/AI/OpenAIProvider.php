<?php

namespace Draad\ContentGenerator\AI;

class OpenAIProvider implements ProviderInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option( 'dcg_openai_api_key', '' );
    }

    public function generate( string $prompt ): string
    {
        if ( empty( $this->apiKey ) ) {
            throw new \RuntimeException( 'OpenAI API key is niet ingesteld.' );
        }

        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            [
				'timeout' => 120,
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->apiKey,
				],
				'body'    => wp_json_encode(
                    [
						'model'    => 'gpt-4o',
						'messages' => [
							[
								'role'    => 'user',
								'content' => $prompt,
							],
						],
					]
                ),
			]
        );

        if ( is_wp_error( $response ) ) {
            throw new \RuntimeException( 'OpenAI API fout: ' . $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['choices'][0]['message']['content'] ) ) {
            throw new \RuntimeException( 'Ongeldig antwoord van OpenAI API.' );
        }

        return $body['choices'][0]['message']['content'];
    }
}
