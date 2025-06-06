import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import Edit from './edit';
import './editor.scss';
import './style.scss';
import metadata from './block.json';

registerBlockType( metadata.name, {
    edit: Edit,
} ); 