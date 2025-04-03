# Silverstripe Instagram Feed 

Add an Instagram Feed to you Silverstripe website that can be used anywhere in your site.

### Thanks
Special thanks to [@Lerni](https://github.com/Lerni) and the [instagram-basic-display-feed-element](https://github.com/lerni/instagram-basic-display-feed-element) for his work on [instagram-basic-display-feed-element]. This project builds upon and refactors his implementation.

## Installation

```bash
composer require xddesigners/silverstripe-instagram-feed
```

## Requirements
* Silverstripe 5.x

## Usage

Setup you Instagram App ID and Secret in your CMS -> Settings -> Instagram.

Run provided task to update your Instagram feed.
sake 
```bash
sake dev/tasks/XD-InstagramFeed-Tasks-InstagramUpdateTask
```

The feed data is stored in the database.

```bash

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