(function($){
    "use strict";

    $(document).ready(function(){
        // add edit button on the bottom of visible attributes on the cart item
        $('.woocommerce-cart-form__cart-item .variation').append('<div class="uvcw-edit"><i class="dashicons dashicons-edit"></i>'+uvcw.edit+'</div>');
    });

    var textToHTML= function (str) {

        var dom = document.createElement('div');
        dom.innerHTML = str;
        return dom;
    
    };

    $(document).on('click', '.uvcw-edit', function(){
        var product_url = $(this).closest('.woocommerce-cart-form__cart-item').find('.product-name a').attr('href');

        Swal.fire({
            html: `
            <div class="uvcw-swal-content">
                <div class="uvcw-swal-loader">
                    <div class="uvcw-spinner"></div>
                </div>
            </div>
            `,
            showClass: {
                popup: 'uvcw-swal'
            },
            didOpen: function(){
                if ( product_url ) {
                    $.get(product_url, function(response){
                        if ( response ) {
                            $('.uvcw-swal-loader').hide();
                            $('.uvcw-swal-content').append(textToHTML(response));
                        }
                    });
                }
            },
            showConfirmButton: false,
            showCloseButton: true,
        })
    });

})(jQuery);