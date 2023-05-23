(function($){
    "use strict";

    $(document).ready(function(){
        

    });

    var textToHTML= function (str) {

        var dom = document.createElement('div');
        dom.innerHTML = str;
        return dom;
    
    };

    $(document).on('click', '.uvcw-edit', function(){
        // window.uvcw_last_clicked_btn = $(this)[0];
        var htmlContent = $(this).closest('.woocommerce-cart-form__cart-item').find('.uvcw-popup-source').text();
        var key = $(this).closest('.woocommerce-cart-form__cart-item').find('.uvcw-item-key').val();
        window.$uvcwQuickshopContent = etoiles_open_quickshop_panel(textToHTML(htmlContent), 'uvcw-swal');

        $uvcwQuickshopContent.find('.variations_form').first().wc_variation_form();

        $uvcwQuickshopContent.find('.woocommerce-variation-add-to-cart .single_add_to_cart_button').attr('data-key', key).text(uvcw.update);

        $(document.body).trigger('uvcw_popup_opened');
    });

    $(document).on('submit', '.uvcw-product-container .variations_form', function(e){
        e.preventDefault();

        var serialized = $(this).serializeArray();
        if ( serialized ) {
            var form = $(this);
            form.find('.single_add_to_cart_button').removeClass('added').addClass('loading');
            var itm_key = $(this).find('.single_add_to_cart_button').attr('data-key');
            var itm_order = $(this).closest('.uvcw-product-container').attr('data-item-order');

            var formDataObject = {item_key: itm_key};
            $(serialized).each(function(index, obj){
                formDataObject[obj.name] = obj.value;
            });

            var itemKeys = [];
            $('tr[data-item-key]:not(.item-removed)').each(function(){
                itemKeys.push($(this).attr('data-item-key')+$(this).find('input.qty').val());
            });

            var data = {
                action: 'uvcw_update_cart',
                data: formDataObject,
                currentItemKeys: itemKeys,
                currentItemKey: itm_key
            }


            
            $.post(uvcw.ajaxurl, data, function(response){
                if ( $(response.html).find('tr[data-item-key]').length ) {
                    // firstly, remove all items except removed items
                    $('tr[data-item-key]:not(.item-removed)').remove();

                    var $oldTbodyHtml = $('.cart-items-area .shop_table tbody').clone();

                    // make the tbody entirely empty
                    $('.cart-items-area .shop_table tbody').html('');

                    // now we will re construct the items from the response.html we have,
                    // but we will respect the position of removed items.
                    var insertAt = 0;
                    var i = 0;
                    while( i < $(response.html).find('tr[data-item-key]').length ) {
                        // if this sequence is for a removed item, append the removed item row.
                        if ( $oldTbodyHtml.find('[data-item-order="'+insertAt+'"]').length ) {
                            $('.cart-items-area .shop_table tbody').append($oldTbodyHtml.find('[data-item-order="'+insertAt+'"]'));
                            insertAt++;
                            $oldTbodyHtml.find('[data-item-order="'+insertAt+'"]').remove();
                        }
                        else {

                            var $current = $(response.html).find('tr[data-item-key]').eq(i);
                            $current.attr('data-item-order', insertAt);
                            $('.cart-items-area .shop_table tbody').append($current);
                            i++;
                            insertAt++;
                        }
                    }

                    // if there are more removed row items left, insert them as well.
                    if ( $oldTbodyHtml.find('[data-item-order]').length ) {
                        $oldTbodyHtml.find('[data-item-order]').each(function(){
                            $(this).attr('data-item-order', insertAt);
                            $('.cart-items-area .shop_table tbody').append($(this));
                            insertAt++;
                        });
                    }

                    // success. so close the popup
                    etoiles_close_quickshop_panel($uvcwQuickshopContent);

                    // trigger cart update
                    // $('body').trigger('wc_update_cart');

                    // this function is defined in the enzy-child theme custom.js
                    update_cart_totals();
                }
            });
        }


    })

})(jQuery);