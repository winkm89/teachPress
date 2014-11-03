/**
 * This file contains js functions for the teachpress tinyMCE plugin.
 * 
 * @package teachpress
 * @subpackage js
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

(function() {
    tinymce.PluginManager.add('teachpress_tinymce', function( editor, url ) {
        editor.addButton( 'teachpress_tinymce', {
            text: 'teachPress',
            icon: false,
            type: 'menubutton',
            menu: [
                {
                    text: 'Add document',
                    onclick: function() {
                        editor.windowManager.open( {
                            url: teachpress_editor_url,
                            title: 'teachPress Document Manager',
                            width: 640,
                            height: 480
                        });
                    }
                },
                {
                    text: 'Insert shortcode (courses)',
                    menu: [
                        
                        // [tpcourselist]
                        
                        {
                            text: 'List of courses [tpcourselist]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert a list of courses [tpcourselist]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tp_image',
                                            label: 'Show images',
                                            'values': [
                                                {text: 'none', value: 'none'},
                                                {text: 'left', value: 'left'},
                                                {text: 'right', value: 'right'},
                                                {text: 'bottom', value: 'bottom'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_size',
                                            label: 'Image size in px',
                                            value: '0'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_headline',
                                            label: 'Show headline',
                                            'values': [
                                                {text: 'show', value: '1'},
                                                {text: 'hide', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_text',
                                            label: 'Custom text under the headline',
                                            value: '',
                                            multiline: true,
                                            minWidth: 300,
                                            minHeight: 100
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_term',
                                            label: 'Term',
                                            'values': teachpress_semester // teachpress_semester object is written with tp_write_data_for_tinymce()
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tpcourselist image="' + e.data.tp_image + '" image_size="' + e.data.tp_size + '" headline="' + e.data.tp_headline + '" text="' + e.data.tp_text + '" term="' + e.data.tp_term + '"]');
                                    }
                                });
                            }
                        },
                        
                        // [tpcoursedocs]
                        
                        {
                            text: 'Course documents [tpcoursedocs]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert a list of course documents [tpcoursedocs]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tp_coure_id',
                                            label: 'Select course',
                                            minWidth: 570,
                                            'values': teachpress_courses // teachpress_courses object is written with tp_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_link_class',
                                            label: 'CSS class for links',
                                            value: 'linksecure'
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_date_format',
                                            label: 'Date format',
                                            value: 'd.m.Y'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_show_date',
                                            label: 'Show upload date for documents',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_numbered',
                                            label: 'Use a numbered list',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_headline',
                                            label: 'Show headline',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tpcoursedocs id="' + e.data.tp_coure_id + '" link_class="' + e.data.tp_link_class + '" date_format="' + e.data.tp_date_format + '" show_date="' + e.data.tp_show_date + '" numbered="' + e.data.tp_numbered + '" headline="' + e.data.tp_headline + '"]');
                                    }
                                });
                            }
                        },
                        
                        // [tpcourseinfo]
                        
                        {
                            text: 'Course information [tpcourseinfo]',
                            onclick: function() {                     
                                editor.windowManager.open( {
                                    title: 'Insert course information [tpcourseinfo]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tp_coure_id',
                                            label: 'Select course',
                                            minWidth: 570,
                                            'values': teachpress_courses // teachpress_courses object is written with tp_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_show_meta',
                                            label: 'Show meta data',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tpcourseinfo id="' + e.data.tp_coure_id + '" show_meta="' + e.data.tp_show_meta + '"]');
                                    }
                                });
                            }
                        },
                        
                        // [tpenrollments]
                        
                        {
                            text: 'Enrollment system [tpenrollments]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert enrollment system [tpenrollments]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tp_term',
                                            label: 'Term',
                                            'values': teachpress_semester // teachpress_semester object is written with tp_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_date_format',
                                            label: 'Date format',
                                            value: 'd.m.Y H:i'
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tpenrollments term="' + e.data.tp_term + '" date_format="' + e.data.tp_date_format + '"]');
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    text: 'Insert shortcode (publications)',
                    menu: [
                        
                        // [tplist]
                        
                        {
                            text: 'Publication list [tplist]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert publication list [tplist]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tp_user',
                                            label: 'Select user',
                                            'values': teachpress_pub_user // teachpress_pub_user object is written with tp_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_headline',
                                            label: 'Headline',
                                            'values': [
                                                {text: 'years', value: '1'},
                                                {text: 'publication types', value: '2'},
                                                {text: 'headlines grouped by year then by type', value: '3'},
                                                {text: 'headlines grouped by type then by year', value: '4'},
                                                {text: 'none', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_image',
                                            label: 'Show images',
                                            'values': [
                                                {text: 'none', value: 'none'},
                                                {text: 'left', value: 'left'},
                                                {text: 'right', value: 'right'},
                                                {text: 'bottom', value: 'bottom'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_size',
                                            label: 'Image size in px',
                                            value: '0'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_author_name',
                                            label: 'Style of the author names',
                                            'values': [
                                                {text: 'last (example: van der Vaart, Ludwig)', value: 'last'},
                                                {text: 'initials (example: van der Vaart, Ludwig C)', value: 'initials'},
                                                {text: 'simple (example: Ludwig C. van der Vaart)', value: 'simple'},
                                                {text: 'old (example: Vaart, Ludwig C. van der)', value: 'old'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_editor_name',
                                            label: 'Style of the editor names',
                                            'values': [
                                                {text: 'last (example: van der Vaart, Ludwig)', value: 'last'},
                                                {text: 'initials (example: van der Vaart, Ludwig C)', value: 'initials'},
                                                {text: 'simple (example: Ludwig C. van der Vaart)', value: 'simple'},
                                                {text: 'old (example: Vaart, Ludwig C. van der)', value: 'old'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_style',
                                            label: 'Style of publication list',
                                            'values': [
                                                {text: '1-line-style (numbered)', value: 'numbered'},
                                                {text: '1-line-style', value: 'simple'},
                                                {text: '4-line-style (numbered)', value: 'std_num'},
                                                {text: '4-line-style', value: 'std'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_link_style',
                                            label: 'Style of publication links',
                                            'values': [
                                                {text: 'inline', value: 'inline'},
                                                {text: 'images', value: 'images'},
                                                {text: 'direct', value: 'direct'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_entries_per_page',
                                            label: 'Entries per page',
                                            value: '50'
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tplist user="' + e.data.tp_user + '" headline="' + e.data.tp_headline + '" image="' + e.data.tp_image + '" image_size="' + e.data.tp_size + '" author_name="' + e.data.tp_author_name + '" editor_name="' + e.data.tp_editor_name + '" style="' + e.data.tp_style + '" link_style="' + e.data.tp_link_style + '" entries_per_page="' + e.data.tp_entries_per_page + '"]');
                                    }
                                });
                            }
                        },
                        
                        // [tpcloud]
                        
                        {
                            text: 'Publication list with tag cloud [tpcloud]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert publication list with tag cloud [tpcloud]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tp_user',
                                            label: 'Select user',
                                            'values': teachpress_pub_user // teachpress_pub_user object is written with tp_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_headline',
                                            label: 'Headline',
                                            'values': [
                                                {text: 'years', value: '1'},
                                                {text: 'publication types', value: '2'},
                                                {text: 'headlines grouped by year then by type', value: '3'},
                                                {text: 'headlines grouped by type then by year', value: '4'},
                                                {text: 'none', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_max_size',
                                            label: 'Max. font size in the tag cloud',
                                            value: '35'
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_min_size',
                                            label: 'Min. font size in the tag cloud',
                                            value: '11'
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_tag_limit',
                                            label: 'Number of tags',
                                            value: '30'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_show_tags_as',
                                            label: 'Show tag filter as',
                                            'values': [
                                                {text: 'cloud', value: 'cloud'},
                                                {text: 'pulldown', value: 'pulldown'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_image',
                                            label: 'Show images',
                                            'values': [
                                                {text: 'none', value: 'none'},
                                                {text: 'left', value: 'left'},
                                                {text: 'right', value: 'right'},
                                                {text: 'bottom', value: 'bottom'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_size',
                                            label: 'Image size in px',
                                            value: '0'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_author_name',
                                            label: 'Style of the author names',
                                            'values': [
                                                {text: 'last (example: van der Vaart, Ludwig)', value: 'last'},
                                                {text: 'initials (example: van der Vaart, Ludwig C)', value: 'initials'},
                                                {text: 'simple (example: Ludwig C. van der Vaart)', value: 'simple'},
                                                {text: 'old (example: Vaart, Ludwig C. van der)', value: 'old'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_editor_name',
                                            label: 'Style of the editor names',
                                            'values': [
                                                {text: 'last (example: van der Vaart, Ludwig)', value: 'last'},
                                                {text: 'initials (example: van der Vaart, Ludwig C)', value: 'initials'},
                                                {text: 'simple (example: Ludwig C. van der Vaart)', value: 'simple'},
                                                {text: 'old (example: Vaart, Ludwig C. van der)', value: 'old'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_style',
                                            label: 'Style of publication list',
                                            'values': [
                                                {text: '1-line-style (numbered)', value: 'numbered'},
                                                {text: '1-line-style', value: 'simple'},
                                                {text: '4-line-style (numbered)', value: 'std_num'},
                                                {text: '4-line-style', value: 'std'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_link_style',
                                            label: 'Style of publication links',
                                            'values': [
                                                {text: 'inline', value: 'inline'},
                                                {text: 'images', value: 'images'},
                                                {text: 'direct', value: 'direct'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_entries_per_page',
                                            label: 'Entries per page',
                                            value: '50'
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tpcloud user="' + e.data.tp_user + '" headline="' + e.data.tp_headline + '" max_size="' + e.data.tp_max_size + '" min_size="' + e.data.tp_min_size + '" tag_limit="' + e.data.tp_tag_limit + '" show_tags_as="' + e.data.tp_show_tags_as + '" image="' + e.data.tp_image + '" image_size="' + e.data.tp_size + '" author_name="' + e.data.tp_author_name + '" editor_name="' + e.data.tp_editor_name + '" style="' + e.data.tp_style + '" link_style="' + e.data.tp_link_style + '" entries_per_page="' + e.data.tp_entries_per_page + '"]');
                                    }
                                });
                            }
                        },
                        
                        // [tpsearch]
                        
                        {
                            text: 'Publication search [tpsearch]',
                            onclick: function() {
                                 editor.windowManager.open( {
                                    title: 'Insert publication search [tpsearch]',
                                    body: [
                                        {
                                            type: 'textbox',
                                            name: 'tp_entries_per_page',
                                            label: 'Entries per page',
                                            value: '20'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_image',
                                            label: 'Show images',
                                            'values': [
                                                {text: 'none', value: 'none'},
                                                {text: 'left', value: 'left'},
                                                {text: 'right', value: 'right'},
                                                {text: 'bottom', value: 'bottom'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_size',
                                            label: 'Image size in px',
                                            value: '0'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_author_name',
                                            label: 'Style of the author names',
                                            'values': [
                                                {text: 'last (example: van der Vaart, Ludwig)', value: 'last'},
                                                {text: 'initials (example: van der Vaart, Ludwig C)', value: 'initials'},
                                                {text: 'simple (example: Ludwig C. van der Vaart)', value: 'simple'},
                                                {text: 'old (example: Vaart, Ludwig C. van der)', value: 'old'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_editor_name',
                                            label: 'Style of the editor names',
                                            'values': [
                                                {text: 'last (example: van der Vaart, Ludwig)', value: 'last'},
                                                {text: 'initials (example: van der Vaart, Ludwig C)', value: 'initials'},
                                                {text: 'simple (example: Ludwig C. van der Vaart)', value: 'simple'},
                                                {text: 'old (example: Vaart, Ludwig C. van der)', value: 'old'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_style',
                                            label: 'Style of publication list',
                                            'values': [
                                                {text: '1-line-style (numbered)', value: 'numbered'},
                                                {text: '1-line-style', value: 'simple'},
                                                {text: '4-line-style (numbered)', value: 'std_num'},
                                                {text: '4-line-style', value: 'std'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_link_style',
                                            label: 'Style of publication links',
                                            'values': [
                                                {text: 'inline', value: 'inline'},
                                                {text: 'images', value: 'images'},
                                                {text: 'direct', value: 'direct'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tp_as_filter',
                                            label: 'Show all publications by default',
                                            'values': [
                                                {text: 'No', value: 'false'},
                                                {text: 'Yes', value: 'true'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tp_date_format',
                                            label: 'Date format',
                                            value: 'd.m.Y'
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        editor.insertContent( '[tpsearch entries_per_page="' + e.data.tp_entries_per_page + '" image="' + e.data.tp_image + '" image_size="' + e.data.tp_size + '" author_name="' + e.data.tp_author_name + '" editor_name="' + e.data.tp_editor_name + '" style="' + e.data.tp_style + '" link_style="' + e.data.tp_link_style + '" as_filter="' + e.data.tp_as_filter + '" date_format="' + e.data.tp_date_format + '"]');
                                    }
                                });
                            }
                        }
                    ]
                }
            ]
        });
    });
})();