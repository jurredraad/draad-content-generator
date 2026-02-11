<?php

namespace Draad\ContentGenerator\Admin;

use Draad\ContentGenerator\ACF\FieldReader;

class GeneratorPage
{
    private const PAGE_SLUG = 'draad-content-generator';

    public function register(): void
    {
        add_menu_page(
            'Content Generator',
            'Content Generator',
            'edit_posts',
            self::PAGE_SLUG,
            [ $this, 'renderPage' ],
            'dashicons-edit-large',
            30
        );

        add_action( 'wp_ajax_dcg_get_field_groups', [ $this, 'ajaxGetFieldGroups' ] );
        add_action( 'wp_ajax_dcg_generate', [ $this, 'ajaxGenerate' ] );
    }

    /**
     * AJAX: haal ACF field groups op voor een post type.
     */
    public function ajaxGetFieldGroups(): void
    {
        check_ajax_referer( 'dcg_generate', 'nonce' );

        $postType = sanitize_text_field( $_POST['post_type'] ?? '' );

        if ( empty( $postType ) ) {
            wp_send_json_error( 'Geen post type opgegeven.' );
        }

        $fieldReader = new FieldReader();
        $groups = $fieldReader->getFieldGroupsForPostType( $postType );

        wp_send_json_success( $groups );
    }

    /**
     * AJAX: genereer content. Wordt afgehandeld door ContentGenerator in Fase 6.
     */
    public function ajaxGenerate(): void
    {
        check_ajax_referer( 'dcg_generate', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Onvoldoende rechten.' );
        }

        $postType   = sanitize_text_field( $_POST['post_type'] ?? '' );
        $title      = sanitize_text_field( $_POST['title'] ?? '' );
        $prompt     = sanitize_textarea_field( $_POST['prompt'] ?? '' );
        $provider   = sanitize_text_field( $_POST['provider'] ?? '' );
        $groupKeys  = array_map( 'sanitize_text_field', $_POST['field_groups'] ?? [] );

        if ( empty( $postType ) || empty( $prompt ) || empty( $groupKeys ) ) {
            wp_send_json_error( 'Vul alle verplichte velden in.' );
        }

        try {
            $generator = new \Draad\ContentGenerator\Generator\ContentGenerator();
            $postId = $generator->generate( $postType, $title, $prompt, $provider, $groupKeys );

            wp_send_json_success( [
                'post_id'  => $postId,
                'edit_url' => get_edit_post_link( $postId, 'raw' ),
            ] );
        } catch ( \Throwable $e ) {
            wp_send_json_error( $e->getMessage() );
        }
    }

    public function renderPage(): void
    {
        $postTypes = get_post_types( [ 'public' => true ], 'objects' );
        $defaultProvider = get_option( 'dcg_default_provider', 'claude' );
        ?>
        <div class="wrap">
            <h1>Content Generator</h1>

            <div id="dcg-generator-form">
                <table class="form-table">
                    <tr>
                        <th><label for="dcg-post-type">Post type</label></th>
                        <td>
                            <select id="dcg-post-type" name="post_type">
                                <option value="">-- Selecteer --</option>
                                <?php foreach ( $postTypes as $pt ) : ?>
                                    <option value="<?php echo esc_attr( $pt->name ); ?>">
                                        <?php echo esc_html( $pt->label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="dcg-title">Titel</label></th>
                        <td>
                            <input type="text" id="dcg-title" name="title" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th>ACF Field Groups</th>
                        <td id="dcg-field-groups">
                            <em>Selecteer eerst een post type.</em>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="dcg-provider">AI Provider</label></th>
                        <td>
                            <select id="dcg-provider" name="provider">
                                <option value="claude" <?php selected( $defaultProvider, 'claude' ); ?>>Claude (Anthropic)</option>
                                <option value="openai" <?php selected( $defaultProvider, 'openai' ); ?>>OpenAI</option>
                                <option value="gemini" <?php selected( $defaultProvider, 'gemini' ); ?>>Google Gemini</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="dcg-prompt">Prompt</label></th>
                        <td>
                            <textarea id="dcg-prompt" name="prompt" rows="8" class="large-text"></textarea>
                            <p class="description">Beschrijf welke content de AI moet genereren.</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="button" id="dcg-generate-btn" class="button button-primary">
                        Genereer content
                    </button>
                    <span id="dcg-spinner" class="spinner"></span>
                </p>

                <div id="dcg-result" style="display:none;">
                    <div class="notice" id="dcg-result-notice">
                        <p id="dcg-result-message"></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
