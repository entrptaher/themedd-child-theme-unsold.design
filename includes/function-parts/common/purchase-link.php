<?php

/**
 * Download purchase link
 * https://docs.easydigitaldownloads.com/article/252-show-purchase-buttons-in-template-files-with-eddgetpurchaselink
 * @since 1.0.0
 */
function vanila_themedd_edd_purchase_link($download_id)
{  
    if(!$download_id) $download_id = get_the_ID();
    
    if (get_post_meta($download_id, '_edd_hide_purchase_link', true)) {
        return; // Do not show if auto output is disabled
    }
    $options = array('download_id' => $download_id);
    if(is_sold_out()){
        $options['class'] = 'sold-out';
    }
    if(is_service()){
        $options['class'] = 'order-service';
        $options['text'] = 'Order';
    }
    echo edd_get_purchase_link($options);
}