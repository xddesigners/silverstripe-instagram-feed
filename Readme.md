# Silverstripe Instagram Feed 

Add an Instagram Feed to you Silverstripe website that can be used anywhere in your site.

### Thanks
Special thanks to [@Lerni](https://github.com/Lerni) and the [instagram-basic-display-feed-element](https://github.com/lerni/instagram-basic-display-feed-element) for his work on [instagram-basic-display-feed-element]. This project builds upon and refactors their implementation.

## Installation

```bash
# Silverstripe 4.x
composer require xddesigners/silverstripe-instagram-feed:dev-ss4

# Silverstripe 5.x
composer require xddesigners/silverstripe-instagram-feed
```

## Requirements
* Silverstripe 4.x

## Usage

Setup you Instagram App ID and Secret in your CMS -> Settings -> Instagram.

Use the provided templates in your website.

```env

```.ss-file
<% include XD\InstagramFeed\Includes\InstagramMedia %>
or
<% include XD\InstagramFeed\Includes\InstagramMedia_grid %>
or
<% include XD\InstagramFeed\Includes\InstagramMedia_swiper %>
Note: you need to add your own swiper js code.
```