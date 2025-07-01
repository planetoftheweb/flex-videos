import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('flex-videos/grid', {
    edit({ attributes, setAttributes }) {
        const { count, hashtag, columns } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Video Grid Settings', 'flex-videos')}>
                        <RangeControl
                            label={__('Number of Videos', 'flex-videos')}
                            value={count}
                            onChange={(value) => setAttributes({ count: value })}
                            min={1}
                            max={50}
                        />
                        <RangeControl
                            label={__('Columns', 'flex-videos')}
                            value={columns}
                            onChange={(value) => setAttributes({ columns: value })}
                            min={1}
                            max={6}
                        />
                        <TextControl
                            label={__('Filter by Hashtag (optional)', 'flex-videos')}
                            value={hashtag}
                            onChange={(value) => setAttributes({ hashtag: value })}
                            placeholder={__('e.g., #webdev', 'flex-videos')}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <div style={{
                        border: '2px dashed #ccc',
                        padding: '20px',
                        textAlign: 'center',
                        backgroundColor: '#f9f9f9',
                        borderRadius: '4px'
                    }}>
                        <div style={{ fontSize: '24px', marginBottom: '10px' }}>📹</div>
                        <h3 style={{ margin: '0 0 10px 0', color: '#333' }}>
                            {__('Flex Videos Grid', 'flex-videos')}
                        </h3>
                        <p style={{ margin: '0', color: '#666', fontSize: '14px' }}>
                            {hashtag ? 
                                __(`Showing ${count} videos filtered by "${hashtag}" in ${columns} columns`, 'flex-videos') :
                                __(`Showing ${count} latest videos in ${columns} columns`, 'flex-videos')
                            }
                        </p>
                        <small style={{ color: '#999', display: 'block', marginTop: '10px' }}>
                            {__('Configure options in the sidebar →', 'flex-videos')}
                        </small>
                    </div>
                </div>
            </>
        );
    },
    save() {
        return null; // Server-side rendering
    }
});
