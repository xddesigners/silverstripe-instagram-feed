<a href="$permalink" target="_blank" rel="noopener" class="d-flex h-100 justify-content-center align-items-center rounded overflow-hidden">
    <figure class="m-0">
        <% if $media_type == "VIDEO" %>
            <video class="pe-none d-block rounded" muted poster="$thumbnail_url" loop autoplay playsinline style="width:100%">
                <source src="$media_url" type="video/mp4">
            </video>
        <% else_if $media_type == "IMAGE" %>
            <img class="img-fluid" loading="lazy" src="$media_url" alt="$caption" />
        <% end_if %>
        <% if $caption %>
            <figcaption class="position-absolute d-block bottom-0 start-0 end-0 m-2 p-2 rounded small text-truncate text-white bg-gray-500 bg-opacity-75">
                {$caption_short} <span data-feather="instagram"></span>
            </figcaption>
        <% end_if %>
    </figure>
</a>