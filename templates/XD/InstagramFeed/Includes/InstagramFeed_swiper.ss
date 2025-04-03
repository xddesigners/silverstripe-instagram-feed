<div class="instagram-feed-swiper swiper-container overflow-hidden">
    <div class="swiper-wrapper bg-gray-100 align-items-stretch">
        <% loop $InstagramMedia(12) %>
            <% if $media_type == "CAROUSEL_ALBUM" %>
                <div class="swiper-slide h-100 $media_type.LowerCase">
                    <% include XD\InstagramFeed\Includes\InstagramMediaCarousel %>
                </div>
            <% else %>
                <div class="swiper-slide $media_type.LowerCase h-100 bg-gray-500 rounded">
                    <% include XD\InstagramFeed\Includes\InstagramMedia %>
                </div>
            <% end_if %>
        <% end_loop %>
    </div>
    <% include XD\InstagramFeed\Includes\InstagramFeedNavigation %>
</div>
