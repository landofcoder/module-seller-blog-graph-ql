# Magento 2 Module Lofmp BlogGraphQl

    ``landofcoder/module-seller-blog-graphql``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
magento 2 blog graphql extension for extension Seller Blog

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Lofmp`
 - Enable the module by running `php bin/magento module:enable Lofmp_BlogGraphQl`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require landofcoder/module-seller-blog-graphql`
 - enable the module by running `php bin/magento module:enable Lofmp_BlogGraphQl`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration


## Queries

1. Query get Blog Archives List

```
{
  blogArchive {
	time
    count 
  }
}
```

2. Query get Blog Posts List

```
{
  blogPosts(
    filter: {}
    sort: {
      creation_time: DESC
    }
    pageSize: 10
    currentPage: 1
  ) {
    total_count
    items {
      post_id
      title
      identifier
      short_content
      image
      image_type
      image_video_type
      image_video_id
      thumbnail
      thumbnail_type
      thumbnail_video_type
      thumbnail_video_id
      hits
      creation_time
      update_time
      like
      real_image_url
      real_thumbnail_url
      comment_count
      tags {
        total_count
        items {
          name
          alias
        }
      }
      categories {
        total_count
        items {
          category_id
          name
          identifier
        }
      }
    }
  }
}
```

3. Query get Blog Post Detail

```
{
    blogPost(post_id: 1)
}
```

. Blog Config

```
{
  storeConfig {
    Blog {
      general_settings_enable
    }
  }
}
```
