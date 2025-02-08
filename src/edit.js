import { useState } from '@wordpress/element';
import { Button, TextControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { dispatch } from '@wordpress/data';

export default function Edit({ attributes, setAttributes, clientId }) {
    const [loading, setLoading] = useState(false);
    const blockProps = useBlockProps();

    function generatePattern() {
        setLoading(true);
        apiFetch({
            url: patternpalNonce.ajaxurl, // Ensure correct AJAX URL
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'pattern_pal_generate_pattern',
                security: patternpalNonce.nonce,
                prompt: attributes.prompt
            })
        }).then(response => {
            setLoading(false);
            if (response.success) {
                // Replace the block with the generated pattern
                insertBlockPattern(response.data.pattern);
            } else {
                alert('Error: ' + response.data.message);
            }
        }).catch(error => {
            setLoading(false);
            console.error("AJAX Error:", error);
            alert('AJAX Request Failed');
        });
    }

    function insertBlockPattern(pattern) {
        // Replace this block with the new block pattern
        const { removeBlock, insertBlocks } = dispatch('core/block-editor');

        // Remove the existing block
        removeBlock(clientId);

        // Insert the new block pattern
        insertBlocks(wp.blocks.parse(pattern));
    }

    return (
        <div {...blockProps}>
            <TextControl
                label="Describe your block pattern"
                value={attributes.prompt}
                onChange={(val) => setAttributes({ prompt: val })}
            />
            <Button variant="primary" onClick={generatePattern} disabled={loading}>
                {loading ? 'Generating...' : 'Generate'}
            </Button>
        </div>
    );
}
