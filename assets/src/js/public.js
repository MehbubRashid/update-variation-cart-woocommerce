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

            var formDataObject = {item_key: itm_key};
            $(serialized).each(function(index, obj){
                formDataObject[obj.name] = obj.value;
            });

            var data = {
                action: 'uvcw_update_cart',
                data: formDataObject
            }


            
            $.post(uvcw.ajaxurl, data, function(response){
                if ( ('success' in response) && response.success ) {
                    // that means the product has been removed. now we will send another ajax to add it in cart
                    var product_url = form.attr('action');
                    var data = form.serialize();
                    if( !form.hasClass('variations_form') && !form.hasClass('grouped_form') ){
                        data += '&add-to-cart=' + form.find('[name="add-to-cart"]').val()
                    }
                    var qty = $('input[name="cart['+itm_key+'][qty]"]').val();
                    data = data.replace(/quantity=[1-9]*/, 'quantity='+qty);

                    // success. so close the popup
                    etoiles_close_quickshop_panel($uvcwQuickshopContent);

                    // trigger cart update
                    $('body').trigger('wc_update_cart');
                }
            });
        }


    })

})(jQuery);