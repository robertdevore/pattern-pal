(function(wp) {
    var el = wp.element.createElement;
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var Button = wp.components.Button;
    var TextControl = wp.components.TextControl;
    var apiFetch = wp.apiFetch;
    var useState = wp.element.useState;
    
    function PatternPalSidebar() {
        var [prompt, setPrompt] = useState('');
        var [loading, setLoading] = useState(false);
        var [response, setResponse] = useState('');

        function generatePattern() {
            setLoading(true);
            apiFetch({
                url: patternpalNonce.ajaxurl,
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'pattern_pal_generate_pattern',
                    security: patternpalNonce.nonce,
                    prompt: prompt
                })
            }).then(response => {
                setLoading(false);
                if (response.success) {
                    setResponse(response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            }).catch(error => {
                setLoading(false);
                console.error("AJAX Error:", error);
            });
        }

        return el(
            PluginSidebar,
            { name: 'pattern-pal-sidebar', icon: 'welcome-learn-more', title: 'Pattern Pal - Pattern Generator' },
            el(TextControl, {
                label: 'Describe your block pattern',
                value: prompt,
                onChange: setPrompt
            }),
            el(Button, { isPrimary: true, onClick: generatePattern, disabled: loading }, loading ? 'Generating...' : 'Generate'),
            response ? el('pre', null, JSON.stringify(response, null, 2)) : null
        );
    }

    registerPlugin('pattern-pal-sidebar', { render: PatternPalSidebar });

})(window.wp);
