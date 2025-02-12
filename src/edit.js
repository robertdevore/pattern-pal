import { useState } from '@wordpress/element';
import { Button, TextControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { dispatch, useDispatch } from '@wordpress/data';

export default function Edit({ attributes, setAttributes, clientId }) {
    const [loading, setLoading] = useState(false);
    const blockProps = useBlockProps();
    // Destructure createNotice from the core/notices store.
    const { createNotice } = useDispatch( 'core/notices' );

    function generatePattern() {
        setLoading(true);
        apiFetch({
            url: patternpalNonce.ajaxurl,
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'pattern_pal_generate_pattern',
                security: patternpalNonce.nonce,
                prompt: attributes.prompt
            })
        })
        .then(response => {
            setLoading(false);
            if (response.success) {
                // Replace the block with the generated pattern
                insertBlockPattern(response.data.pattern);
            } else {
                // Check if the error is due to a missing API key.
                if (response.data.message && response.data.message.includes('No API key')) {
                    createNotice(
                        'error',
                        'No API key found. Please add your OpenAI API key in the Pattern Pal settings.',
                        {
                            isDismissible: true,
                            actions: [
                                {
                                    label: 'Go to Settings',
                                    onClick: () => {
                                        window.location.href = patternpalNonce.settingsUrl;
                                    }
                                }
                            ]
                        }
                    );
                } else {
                    // For any other errors, show a simple error notice.
                    createNotice('error', 'Error: ' + response.data.message, { isDismissible: true });
                }
            }
        })
        .catch(error => {
            setLoading(false);
            console.error("AJAX Error:", error);
            createNotice(
                'error',
                'The request failed. It appears your OpenAI API key may be missing or invalid. Please add your key in the Pattern Pal settings.',
                {
                    isDismissible: true,
                    actions: [
                        {
                            label: 'Go to Settings',
                            onClick: () => {
                                window.location.href = patternpalNonce.settingsUrl;
                            }
                        }
                    ]
                }
            );
        });
    }

    function insertBlockPattern(pattern) {
        // Replace this block with the new block pattern.
        const { removeBlock, insertBlocks } = dispatch('core/block-editor');
        removeBlock(clientId);
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
