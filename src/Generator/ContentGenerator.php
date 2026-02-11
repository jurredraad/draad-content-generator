<?php

namespace Draad\ContentGenerator\Generator;

use Draad\ContentGenerator\ACF\FieldReader;
use Draad\ContentGenerator\ACF\FieldWriter;
use Draad\ContentGenerator\AI\ProviderInterface;
use Draad\ContentGenerator\AI\ClaudeProvider;
use Draad\ContentGenerator\AI\OpenAIProvider;
use Draad\ContentGenerator\AI\GeminiProvider;

class ContentGenerator
{
    private FieldReader $fieldReader;
    private FieldWriter $fieldWriter;

    public function __construct()
    {
        $this->fieldReader = new FieldReader();
        $this->fieldWriter = new FieldWriter();
    }

    /**
     * Genereer een nieuwe post met AI-gegenereerde ACF content.
     *
     * @param string        $postType   Post type slug
     * @param string        $title      Post titel
     * @param string        $prompt     Gebruikersinstructies voor de AI
     * @param string        $provider   Provider slug (claude, openai, gemini)
     * @param array<string> $groupKeys  ACF field group keys
     * @return int Post ID
     */
    public function generate(
        string $postType,
        string $title,
        string $prompt,
        string $provider,
        array $groupKeys
    ): int {
        // 1. Haal ACF veldstructuur op
        $schema = $this->fieldReader->getFieldSchema( $groupKeys );

        if ( empty( $schema ) ) {
            throw new \RuntimeException( 'Geen ACF velden gevonden voor de geselecteerde field groups.' );
        }

        // 2. Bouw prompt op voor de AI
        $aiPrompt = $this->buildPrompt( $prompt, $schema );

        // 3. Stuur naar AI provider
        $aiProvider = $this->resolveProvider( $provider );
        $response   = $aiProvider->generate( $aiPrompt );

        // 4. Parse JSON response
        $data = $this->parseResponse( $response );

        // 5. Maak post aan
        $postId = wp_insert_post(
            [
				'post_type'   => $postType,
				'post_title'  => ! empty( $title ) ? $title : ( $data['_post_title'] ?? 'Gegenereerde post' ),
				'post_status' => 'draft',
			],
			true
        );

        if ( is_wp_error( $postId ) ) {
            throw new \RuntimeException( 'Post aanmaken mislukt: ' . $postId->get_error_message() );
        }

        // Verwijder eventueel meegegeven meta-veld uit de data
        unset( $data['_post_title'] );

        // 6. Schrijf ACF velden
        $this->fieldWriter->writeFields( $postId, $data, $schema );

        return $postId;
    }

    /**
     * Bouw een gestructureerde prompt op met het ACF schema.
     */
    private function buildPrompt( string $userPrompt, array $schema ): string
    {
        $schemaJson = wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

        return <<<PROMPT
Je bent een content generator. Genereer content op basis van de onderstaande instructies en vul de ACF velden in volgens het schema.

## Gebruikersinstructies
{$userPrompt}

## ACF Veldstructuur (JSON schema)
{$schemaJson}

## Instructies voor het antwoord
- Geef ALLEEN een geldig JSON object terug, geen extra tekst.
- De keys in je JSON moeten exact overeenkomen met de veldnamen uit het schema.
- Voor flexible_content velden: geef een array van objecten met een "acf_fc_layout" key die de layout naam bevat, plus de sub_fields.
- Voor repeater velden: geef een array van objecten met de sub_field namen als keys.
- Voor select/radio/checkbox velden: gebruik alleen waarden uit de "choices" lijst.
- Voor text/textarea/wysiwyg velden: geef een string terug.
- Velden van type "image", "file", "gallery", "post_object" of "relationship" mag je overslaan (niet opnemen in de JSON).
- Als je een post titel wilt voorstellen, voeg dan een "_post_title" key toe.
PROMPT;
    }

    private function resolveProvider( string $provider ): ProviderInterface
    {
        return match ( $provider ) {
            'claude' => new ClaudeProvider(),
            'openai' => new OpenAIProvider(),
            'gemini' => new GeminiProvider(),
            default  => throw new \RuntimeException( "Onbekende AI provider: {$provider}" ),
        };
    }

    /**
     * Parse de AI response als JSON.
     */
    private function parseResponse( string $response ): array
    {
        // Strip eventuele markdown code blocks
        $response = trim( $response );

        if ( str_starts_with( $response, '```' ) ) {
            $response = preg_replace( '/^```(?:json)?\s*/i', '', $response );
            $response = preg_replace( '/\s*```\s*$/', '', $response );
        }

        $data = json_decode( $response, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new \RuntimeException( 'AI response is geen geldige JSON: ' . json_last_error_msg() );
        }

        return $data;
    }
}
