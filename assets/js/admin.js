(function( $ ) {
    'use strict';

    var $postType    = $( '#dcg-post-type' );
    var $fieldGroups = $( '#dcg-field-groups' );
    var $generateBtn = $( '#dcg-generate-btn' );
    var $spinner     = $( '#dcg-spinner' );
    var $result      = $( '#dcg-result' );
    var $notice      = $( '#dcg-result-notice' );
    var $message     = $( '#dcg-result-message' );

    // Laad field groups bij post type wijziging
    $postType.on( 'change', function() {
        var postType = $( this ).val();

        if ( ! postType ) {
            $fieldGroups.html( '<em>Selecteer eerst een post type.</em>' );
            return;
        }

        $fieldGroups.html( '<em>Laden...</em>' );

        $.post( dcgAdmin.ajaxUrl, {
            action: 'dcg_get_field_groups',
            nonce: dcgAdmin.nonce,
            post_type: postType
        }, function( response ) {
            if ( ! response.success || response.data.length === 0 ) {
                $fieldGroups.html( '<em>Geen ACF field groups gevonden voor dit post type.</em>' );
                return;
            }

            var html = '';
            $.each( response.data, function( i, group ) {
                html += '<label style="display:block; margin-bottom:5px;">';
                html += '<input type="checkbox" name="field_groups[]" value="' + group.key + '" checked /> ';
                html += group.title;
                html += '</label>';
            } );

            $fieldGroups.html( html );
        } );
    } );

    // Genereer content
    $generateBtn.on( 'click', function() {
        var postType   = $postType.val();
        var title      = $( '#dcg-title' ).val();
        var prompt     = $( '#dcg-prompt' ).val();
        var provider   = $( '#dcg-provider' ).val();
        var groups     = [];

        $fieldGroups.find( 'input:checked' ).each( function() {
            groups.push( $( this ).val() );
        } );

        if ( ! postType || ! prompt || groups.length === 0 ) {
            alert( 'Vul alle verplichte velden in (post type, field groups, prompt).' );
            return;
        }

        $generateBtn.prop( 'disabled', true );
        $spinner.addClass( 'is-active' );
        $result.hide();

        $.post( dcgAdmin.ajaxUrl, {
            action: 'dcg_generate',
            nonce: dcgAdmin.nonce,
            post_type: postType,
            title: title,
            prompt: prompt,
            provider: provider,
            field_groups: groups
        }, function( response ) {
            $generateBtn.prop( 'disabled', false );
            $spinner.removeClass( 'is-active' );
            $result.show();

            if ( response.success ) {
                $notice.removeClass( 'notice-error' ).addClass( 'notice-success' );
                $message.html(
                    'Post aangemaakt! <a href="' + response.data.edit_url + '">Bekijk en bewerk de post</a>'
                );
            } else {
                $notice.removeClass( 'notice-success' ).addClass( 'notice-error' );
                $message.text( 'Fout: ' + response.data );
            }
        } ).fail( function() {
            $generateBtn.prop( 'disabled', false );
            $spinner.removeClass( 'is-active' );
            $result.show();
            $notice.removeClass( 'notice-success' ).addClass( 'notice-error' );
            $message.text( 'Er is een onverwachte fout opgetreden.' );
        } );
    } );

})( jQuery );
