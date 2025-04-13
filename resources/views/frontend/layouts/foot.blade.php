
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('frontend/assets/js/slick.js') }}"></script>
<script type="text/javascript" src="{{ asset('frontend/assets_f/js/script.js') }}"></script>
<script src="{{ asset('frontend/assets_f/js/jquery-1.11.0.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>


<script>
    $(document).ready(function() {
        // Slick Slider Initialization
        $('.slider-animate').slick({
            autoplay: true,
            speed: 800,
            lazyLoad: 'progressive',
            fade: true,
            dots: false,
        }).slickAnimation();

        $('.product-slick-animated').slick({
            autoplay: true,
            speed: 1000,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            lazyLoad: 'progressive',
            fade: true,
            asNavFor: '.animated-nav',
        }).slickAnimation();

        $('.center-home-slider').slick({
            centerMode: true,
            centerPadding: '100px',
            slidesToShow: 1,
            responsive: [{
                    breakpoint: 769,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '0',
                        slidesToShow: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '10px',
                        slidesToShow: 1
                    }
                }
            ]
        }).slickAnimation();

        // Modal Show
        setTimeout(function() {
            $('#exampleModal').modal('show');
        }, 100);

        // Search Overlay
        function openSearch() {
            document.getElementById("search-overlay").style.display = "block";
        }

        function closeSearch() {
            document.getElementById("search-overlay").style.display = "none";
        }

        // Cart and Wishlist Functions
        class Sanpham {
            constructor(id, quantity) {
                this.id = id;
                this.quantity = quantity;
            }
        }

        function add_notify(msg, status) {
            $.notify({
                icon: 'fa fa-check',
                title: status ? 'Thành Công!' : 'Thất bại!',
                message: msg,
            }, {
                element: 'body',
                position: null,
                type: status ? "info" : "warning",
                allow_dismiss: false,
                newest_on_top: false,
                showProgressbar: true,
                placement: {
                    from: "top",
                    align: "right"
                },
                offset: 20,
                spacing: 10,
                z_index: 1031,
                delay: 2000,
                animate: {
                    enter: 'animated fadeInDown',
                    exit: 'animated fadeOutUp'
                },
                icon_type: 'class',
                template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
                    '<button type="button" aria-hidden="true" class="btn-close" data-notify="dismiss"></button>' +
                    '<span data-notify="icon"></span> ' +
                    '<span data-notify="title">{1}</span> ' +
                    '<span data-notify="message">{2}</span>' +
                    '<div class="progress" data-notify="progressbar">' +
                    '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                    '</div>' +
                    '<a href="{3}" target="{4}" data-notify="url"></a>' +
                    '</div>'
            });
        }

        // Event Delegation for Cart and Wishlist
        $(document).on('click', '#cartEffect, .product-box button .ti-shopping-cart', function() {
            const quantity = $('#quantity').val() || 1;
            const data_send = new Sanpham($(this).attr("data-id"), quantity);
            const dataToSend = {
                _token: "{{ csrf_token() }}",
                product: data_send,
            };

            $.ajax({
                url: "", // Replace with your actual server endpoint URL
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify(dataToSend),
                success: function(response) {
                    add_notify(response.msg, response.status);
                    // Update cart UI if needed
                },
                error: function(error) {
                    console.error("Error:", error);
                }
            });
        });

        $(document).on('click', '#addWishlist, .product-box a .ti-heart, .product-box a .fa-heart', function() {
            const data_send = new Sanpham($(this).attr("data-id"), 0);
            const dataToSend = {
                _token: "{{ csrf_token() }}",
                product: data_send,
            };

            $.ajax({
                url: "", // Replace with your actual server endpoint URL
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify(dataToSend),
                success: function(response) {
                    add_notify(response.msg, response.status);
                },
                error: function(error) {
                    console.error("Error:", error);
                }
            });
        });
    });
</script>
