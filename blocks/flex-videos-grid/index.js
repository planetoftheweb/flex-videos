import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType( 'flex-videos/grid', {
    edit() {
        return __( 'Flex Videos Grid', 'flex-videos' );
    },
    save() {
        return null;
    }
} );
