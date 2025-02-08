import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import Save from './save';

registerBlockType('pattern-pal/pattern-generator', {
    edit: Edit,
    save: Save
});
