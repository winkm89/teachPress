<?php
/**
 * Registers all default awards
 * @since 9.0.0
 */
function tp_register_all_awards() {
    // No Award
    tp_register_award(
        array(
            'award_slug'        => 'none',
            'i18n_singular'     => esc_html__('None','teachpress'),
            'i18n_plural'       => esc_html__('None','teachpress'),
            'icon'              => ''
        ) );
    
    // Best Paper
    tp_register_award(
        array(
            'award_slug'        => 'best',
            'i18n_singular'     => esc_html__('Best Paper','teachpress'),
            'i18n_plural'       => esc_html__('Best Papers','teachpress'),
            'icon'              => 'fas fa-trophy'
        ) );
    
    // Honorable Mention
    tp_register_award(
        array(
            'award_slug'        => 'honorable',
            'i18n_singular'     => esc_html__('Honorable Mention','teachpress'),
            'i18n_plural'       => esc_html__('Honorable Mentions','teachpress'),
            'icon'              => 'fas fa-award'
        ) );

}
