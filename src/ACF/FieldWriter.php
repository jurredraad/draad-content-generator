<?php

namespace Draad\ContentGenerator\ACF;

class FieldWriter
{
    /**
     * Schrijf gegenereerde content naar ACF velden op een post.
     *
     * @param int                  $postId
     * @param array<string, mixed> $data   Veldnaam => waarde, moet matchen met het schema uit FieldReader.
     * @param array<string, mixed> $schema Het schema uit FieldReader, nodig voor flexible content structuur.
     */
    public function writeFields( int $postId, array $data, array $schema ): void
    {
        foreach ( $data as $fieldName => $value ) {
            if ( ! isset( $schema[ $fieldName ] ) ) {
                continue;
            }

            $fieldSchema = $schema[ $fieldName ];

            if ( $fieldSchema['type'] === 'flexible_content' ) {
                $this->writeFlexibleContent( $postId, $fieldName, $value, $fieldSchema );
                continue;
            }

            update_field( $fieldSchema['key'], $value, $postId );
        }
    }

    /**
     * Schrijf flexible content layouts naar post meta.
     *
     * Verwacht $value als array van layouts:
     * [
     *     [ 'acf_fc_layout' => 'layout_name', 'field_name' => 'value', ... ],
     *     ...
     * ]
     */
    private function writeFlexibleContent( int $postId, string $fieldName, array $layouts, array $fieldSchema ): void
    {
        $formatted = [];

        foreach ( $layouts as $layout ) {
            if ( empty( $layout['acf_fc_layout'] ) ) {
                continue;
            }

            $layoutName = $layout['acf_fc_layout'];
            $formattedLayout = [ 'acf_fc_layout' => $layoutName ];

            // Haal sub_fields schema op voor deze layout
            $layoutSchema = $fieldSchema['layouts'][ $layoutName ]['sub_fields'] ?? [];

            foreach ( $layout as $subFieldName => $subValue ) {
                if ( $subFieldName === 'acf_fc_layout' ) {
                    continue;
                }

                $formattedLayout[ $subFieldName ] = $subValue;
            }

            $formatted[] = $formattedLayout;
        }

        update_field( $fieldSchema['key'], $formatted, $postId );
    }
}
