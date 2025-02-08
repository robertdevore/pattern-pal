import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    return (
        <div {...useBlockProps.save()} dangerouslySetInnerHTML={{ __html: attributes.generatedPattern }} />
    );
}
