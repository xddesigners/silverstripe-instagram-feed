<div class="instagram-item-swiper overflow-hidden bg-gray-500 h-100 rounded position-relative">
    <div class="swiper-wrapper">
        <% loop $Children %>
            <div class="swiper-slide $media_type.LowerCase">
                <% include XD\InstagramFeed\Includes\InstagramMedia %>
            </div>
        <% end_loop %>
    </div>
    <% if $Children.Count >= 1 %>
        <div class="swiper-pagination"></div>
    <% end_if %>
</div>