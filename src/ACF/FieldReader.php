<?php

namespace Draad\ContentGenerator\ACF;

class FieldReader
{
    /**
     * Haal alle field groups op die gekoppeld zijn aan een post type.
     *
     * @return array<int, array{ key: string, title: string }>
     */
    public function getFieldGroupsForPostType( string $postType ): array
    {
        $groups = acf_get_field_groups( [
            'post_type' => $postType,
        ] );

        return array_map( fn( array $group ) => [
            'key'   => $group['key'],
            'title' => $group['title'],
        ], $groups );
    }

    /**
     * Haal de veldstructuur op voor geselecteerde field groups.
     * Geeft een JSON-schema-achtige structuur terug die naar de AI gestuurd kan worden.
     *
     * @param array<int, string> $groupKeys
     * @return array<string, mixed>
     */
    public function getFieldSchema( array $groupKeys ): array
    {
        $schema = [];

        foreach ( $groupKeys as $groupKey ) {
            $fields = acf_get_fields( $groupKey );

            if ( ! $fields ) {
                continue;
            }

            foreach ( $fields as $field ) {
                $schema[ $field['name'] ] = $this->parseField( $field );
            }
        }

        return $schema;
    }

    /**
     * Zet een ACF veld om naar een schema-beschrijving.
     */
    private function parseField( array $field ): array
    {
        $parsed = [
            'key'  => $field['key'],
            'type' => $field['type'],
            'label' => $field['label'],
        ];

        // Select, radio, checkbox: keuzes meegeven
        if ( ! empty( $field['choices'] ) ) {
            $parsed['choices'] = $field['choices'];
        }

        // Flexible content: layouts met sub_fields
        if ( $field['type'] === 'flexible_content' && ! empty( $field['layouts'] ) ) {
            $parsed['layouts'] = [];

            foreach ( $field['layouts'] as $layout ) {
                $layoutSchema = [
                    'name'  => $layout['name'],
                    'label' => $layout['label'],
                    'sub_fields' => [],
                ];

                if ( ! empty( $layout['sub_fields'] ) ) {
                    foreach ( $layout['sub_fields'] as $subField ) {
                        $layoutSchema['sub_fields'][ $subField['name'] ] = $this->parseField( $subField );
                    }
                }

                $parsed['layouts'][ $layout['name'] ] = $layoutSchema;
            }
        }

        // Repeater: sub_fields
        if ( $field['type'] === 'repeater' && ! empty( $field['sub_fields'] ) ) {
            $parsed['sub_fields'] = [];

            foreach ( $field['sub_fields'] as $subField ) {
                $parsed['sub_fields'][ $subField['name'] ] = $this->parseField( $subField );
            }
        }

        // Group: sub_fields
        if ( $field['type'] === 'group' && ! empty( $field['sub_fields'] ) ) {
            $parsed['sub_fields'] = [];

            foreach ( $field['sub_fields'] as $subField ) {
                $parsed['sub_fields'][ $subField['name'] ] = $this->parseField( $subField );
            }
        }

        return $parsed;
    }
}
