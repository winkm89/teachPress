/**
 * teachPress Filter block – editor registration
 *
 * Vanilla ES5, no build step required.
 * Works with WordPress 5.8+ (wp-server-side-render, wp-block-editor).
 * Publication types are passed from PHP via wp_localize_script (tpFilterBlock.pubTypes).
 *
 * @package teachpress\js
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since   9.0.13
 */
( function () {
    'use strict';

    var registerBlockType = wp.blocks.registerBlockType;
    var el                = wp.element.createElement;
    var __                = wp.i18n.__;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody         = wp.components.PanelBody;
    var TextControl       = wp.components.TextControl;
    var SelectControl     = wp.components.SelectControl;
    var ToggleControl     = wp.components.ToggleControl;
    var RadioControl      = wp.components.RadioControl;
    var ServerSideRender  = wp.serverSideRender;

    // Publication types passed from PHP via wp_localize_script.
    // Falls back to a single "All types" option if data is unavailable.
    var pubTypeOptions = ( window.tpFilterBlock && window.tpFilterBlock.pubTypes )
        ? window.tpFilterBlock.pubTypes
        : [ { label: __( 'All types', 'teachpress' ), value: 'all' } ];

    registerBlockType( 'teachpress/filter', {
        title:    __( 'teachPress Filter', 'teachpress' ),
        icon:     'filter',
        category: 'widgets',
        supports: { html: false },

        edit: function ( props ) {
            var attr    = props.attributes;
            var setAttr = props.setAttributes;

            return [
                el( InspectorControls, { key: 'controls' },

                    el( PanelBody, { title: __( 'General', 'teachpress' ), initialOpen: true },
                        el( TextControl, {
                            label:    __( 'Title', 'teachpress' ),
                            value:    attr.title,
                            onChange: function ( v ) { setAttr( { title: v } ); }
                        } ),
                        el( RadioControl, {
                            label:    __( 'Mode', 'teachpress' ),
                            selected: attr.mode,
                            options: [
                                { label: __( 'Years', 'teachpress' ), value: 'years' },
                                { label: __( 'Tags',  'teachpress' ), value: 'tags'  }
                            ],
                            onChange: function ( v ) { setAttr( { mode: v } ); }
                        } )
                    ),

                    el( PanelBody, { title: __( 'Publication filter', 'teachpress' ), initialOpen: false },
                        el( SelectControl, {
                            label:    __( 'Publication type', 'teachpress' ),
                            value:    attr.pub_type,
                            options:  pubTypeOptions,
                            onChange: function ( v ) { setAttr( { pub_type: v } ); }
                        } ),
                        attr.mode === 'tags' && el( TextControl, {
                            label:    __( 'Minimum publications per tag', 'teachpress' ),
                            type:     'number',
                            value:    String( attr.min_pubs ),
                            min:      1,
                            onChange: function ( v ) { setAttr( { min_pubs: Math.max( 1, parseInt( v, 10 ) || 1 ) } ); }
                        } )
                    ),

                    el( PanelBody, { title: __( 'Display options', 'teachpress' ), initialOpen: false },
                        el( ToggleControl, {
                            label:    __( 'Highlight active filter', 'teachpress' ),
                            help:     __( 'Adds CSS class "active" and aria-current to the active item', 'teachpress' ),
                            checked:  attr.show_active,
                            onChange: function ( v ) { setAttr( { show_active: v } ); }
                        } ),
                        el( RadioControl, {
                            label:    __( 'Year order', 'teachpress' ),
                            selected: attr.order_years,
                            options: [
                                { label: __( 'Newest first', 'teachpress' ), value: 'DESC' },
                                { label: __( 'Oldest first', 'teachpress' ), value: 'ASC'  }
                            ],
                            onChange: function ( v ) { setAttr( { order_years: v } ); }
                        } ),
                        el( RadioControl, {
                            label:    __( 'Tag order', 'teachpress' ),
                            selected: attr.order_tags,
                            options: [
                                { label: __( 'By relevance (most used first)', 'teachpress' ), value: 'relevance' },
                                { label: __( 'Alphabetical (A → Z)',           'teachpress' ), value: 'alpha'     }
                            ],
                            onChange: function ( v ) { setAttr( { order_tags: v } ); }
                        } )
                    )
                ),

                el( ServerSideRender, {
                    key:        'preview',
                    block:      'teachpress/filter',
                    attributes: attr
                } )
            ];
        },

        save: function () {
            return null; // entirely server-side rendered
        }
    } );

} )();