(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, MediaUpload, RichText } = wp.blockEditor || wp.editor;
    const { PanelBody, Button, TextControl, SelectControl } = wp.components;
    const { Component } = wp.element;

    registerBlockType('tripeak/card-grid', {
        title: 'Card Grid',
        icon: 'grid-view',
        category: 'layout',
        description: 'A responsive 3-column card grid layout',
        supports: { html: false },
        
        attributes: {
            cards: {
                type: 'string',
                default: JSON.stringify([
                    {
                        title: 'Card Title 1',
                        content: 'Add your card content here...',
                        imageUrl: '',
                        imageId: 0,
                        category: 'Sample',
                        linkUrl: '#',
                        buttonText: 'Read More'
                    },
                    {
                        title: 'Card Title 2', 
                        content: 'Add your card content here...',
                        imageUrl: '',
                        imageId: 0,
                        category: 'Sample',
                        linkUrl: '#',
                        buttonText: 'Read More'
                    },
                    {
                        title: 'Card Title 3',
                        content: 'Add your card content here...',
                        imageUrl: '',
                        imageId: 0,
                        category: 'Sample',
                        linkUrl: '#',
                        buttonText: 'Read More'
                    }
                ])
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const cards = JSON.parse(attributes.cards || '[]');

            const updateCard = (index, field, value) => {
                const newCards = [...cards];
                newCards[index][field] = value;
                setAttributes({ cards: JSON.stringify(newCards) });
            };

            const addCard = () => {
                const newCards = [...cards, {
                    title: 'New Card',
                    content: 'Add your card content here...',
                    imageUrl: '',
                    imageId: 0,
                    category: 'Category',
                    linkUrl: '#',
                    buttonText: 'Read More'
                }];
                setAttributes({ cards: JSON.stringify(newCards) });
            };

            const removeCard = (index) => {
                const newCards = cards.filter((_, i) => i !== index);
                setAttributes({ cards: JSON.stringify(newCards) });
            };

            return wp.element.createElement('div', { className: 'tripeak-card-grid-editor' },
                wp.element.createElement(InspectorControls, {},
                    wp.element.createElement(PanelBody, { title: 'Card Grid Settings' },
                        wp.element.createElement(Button, {
                            isPrimary: true,
                            onClick: addCard
                        }, 'Add New Card')
                    )
                ),
                
                wp.element.createElement('div', { className: 'editor-card-grid' },
                    wp.element.createElement('h3', { style: { textAlign: 'center', marginBottom: '20px' } }, 'Card Grid Block'),
                    
                    wp.element.createElement('div', { 
                        className: 'grid grid-3',
                        style: { 
                            display: 'grid', 
                            gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', 
                            gap: '20px',
                            marginBottom: '20px'
                        }
                    },
                        cards.map((card, index) =>
                            wp.element.createElement('div', {
                                key: index,
                                className: 'editor-card',
                                style: {
                                    border: '1px solid #ddd',
                                    borderRadius: '8px',
                                    padding: '15px',
                                    backgroundColor: '#fff'
                                }
                            },
                                wp.element.createElement('div', { style: { marginBottom: '10px' } },
                                    wp.element.createElement('strong', {}, `Card ${index + 1}`),
                                    wp.element.createElement(Button, {
                                        isDestructive: true,
                                        isSmall: true,
                                        onClick: () => removeCard(index),
                                        style: { float: 'right' }
                                    }, 'Remove')
                                ),
                                
                                wp.element.createElement(MediaUpload, {
                                    onSelect: (media) => {
                                        updateCard(index, 'imageUrl', media.url);
                                        updateCard(index, 'imageId', media.id);
                                    },
                                    allowedTypes: ['image'],
                                    value: card.imageId,
                                    render: ({ open }) =>
                                        wp.element.createElement('div', { style: { marginBottom: '10px' } },
                                            card.imageUrl ? 
                                                wp.element.createElement('img', {
                                                    src: card.imageUrl,
                                                    style: { width: '100%', height: '150px', objectFit: 'cover', borderRadius: '4px' }
                                                }) :
                                                wp.element.createElement('div', {
                                                    style: {
                                                        width: '100%',
                                                        height: '150px',
                                                        backgroundColor: '#f0f0f0',
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        justifyContent: 'center',
                                                        borderRadius: '4px',
                                                        cursor: 'pointer'
                                                    },
                                                    onClick: open
                                                }, 'Select Image')
                                        )
                                }),
                                
                                wp.element.createElement(TextControl, {
                                    label: 'Category',
                                    value: card.category,
                                    onChange: (value) => updateCard(index, 'category', value),
                                    style: { marginBottom: '10px' }
                                }),
                                
                                wp.element.createElement(RichText, {
                                    tagName: 'h3',
                                    value: card.title,
                                    onChange: (value) => updateCard(index, 'title', value),
                                    placeholder: 'Card title...',
                                    style: { marginBottom: '10px' }
                                }),
                                
                                wp.element.createElement(RichText, {
                                    tagName: 'p',
                                    value: card.content,
                                    onChange: (value) => updateCard(index, 'content', value),
                                    placeholder: 'Card content...',
                                    style: { marginBottom: '10px' }
                                }),
                                
                                wp.element.createElement(TextControl, {
                                    label: 'Link URL',
                                    value: card.linkUrl,
                                    onChange: (value) => updateCard(index, 'linkUrl', value),
                                    style: { marginBottom: '10px' }
                                }),
                                
                                wp.element.createElement(TextControl, {
                                    label: 'Button Text',
                                    value: card.buttonText,
                                    onChange: (value) => updateCard(index, 'buttonText', value)
                                })
                            )
                        )
                    )
                )
            );
        },

        save: function(props) {
            const { attributes } = props;
            const cards = JSON.parse(attributes.cards || '[]');

            return wp.element.createElement('div', { className: 'custom-card-grid', style: { padding: '2rem 0' } },
                wp.element.createElement('div', { className: 'grid grid-3' },
                    cards.map((card, index) =>
                        wp.element.createElement('article', {
                            key: index,
                            className: 'card'
                        },
                            card.imageUrl ? 
                                wp.element.createElement('div', { className: 'card-image-wrapper' },
                                    wp.element.createElement('a', { href: card.linkUrl },
                                        wp.element.createElement('img', {
                                            src: card.imageUrl,
                                            alt: card.title,
                                            className: 'card-image'
                                        })
                                    )
                                ) :
                                wp.element.createElement('div', {
                                    className: 'card-image-placeholder',
                                    style: {
                                        background: 'linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)',
                                        height: '200px',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center'
                                    }
                                }, wp.element.createElement('i', {
                                    className: 'fas fa-image',
                                    style: { fontSize: '3rem', color: '#ccc' }
                                })),
                            
                            wp.element.createElement('div', { className: 'card-content' },
                                card.category && wp.element.createElement('span', { className: 'card-category' }, card.category),
                                wp.element.createElement('h3', {},
                                    wp.element.createElement('a', { href: card.linkUrl }, card.title)
                                ),
                                wp.element.createElement('div', { className: 'card-excerpt' },
                                    wp.element.createElement('p', {}, card.content)
                                ),
                                wp.element.createElement('div', { className: 'card-footer' },
                                    wp.element.createElement('a', {
                                        href: card.linkUrl,
                                        className: 'btn'
                                    }, card.buttonText)
                                )
                            )
                        )
                    )
                )
            );
        }
    });
})(); 