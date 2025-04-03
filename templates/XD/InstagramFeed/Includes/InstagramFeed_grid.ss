<div class="row">
    <% loop $InstagramMedia(12) %>
        <% if $media_type == "CAROUSEL_ALBUM" %>
            <div class="col-12 col-lg-3 col-md-4 mb-4 $media_type.LowerCase">
                <% include XD\InstagramFeed\Includes\InstagramMediaCarousel %>
            </div>
        <% else %>
            <div class="col-12 col-lg-3 col-md-4 mb-4 $media_type.LowerCase ">
                <div class="bg-gray-500 h-100 rounded position-relative">
                    <% include XD\InstagramFeed\Includes\InstagramMedia %>
                </div>
            </div>
        <% end_if %>
    <% end_loop %>
</div>
