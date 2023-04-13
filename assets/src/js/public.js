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
        var htmlContent = $(this).closest('.woocommerce-cart-form__cart-item').find('.uvcw-popup-source').text();

        Swal.fire({
            html: textToHTML(htmlContent),
            showClass: {
                popup: 'uvcw-swal'
            },
            didOpen: function(){
                
            },
            showConfirmButton: false,
            showCloseButton: true,
            scrollbarPadding: false,
            target: '.site-content'
        })
    });

})(jQuery);