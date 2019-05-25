<?php

/**
 * Remove standard wish list links
 * @return [type] [description]
 * @uses  edd_favorites_load_link()
 */
function vanila_edd_favorites_link()
{
    // remove standard add to wish list link
    remove_action('edd_purchase_link_top', 'edd_favorites_load_link');

    // add our new link
    add_action('edd_purchase_link_end', 'edd_favorites_load_link');
}