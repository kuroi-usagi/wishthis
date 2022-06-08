<?php

/**
 * The wishthis blog.
 */

namespace wishthis;

class Blog
{
    private const ENDPOINT_BASE       = 'https://wishthis.online/src/blog/wp-json/wp/v2';
    private const ENDPOINT_POST       = self::ENDPOINT_BASE . '/posts/%d';
    private const ENDPOINT_POSTS      = self::ENDPOINT_BASE . '/posts';
    private const ENDPOINT_MEDIA      = self::ENDPOINT_BASE . '/media/%d';
    private const ENDPOINT_CATEGORIES = self::ENDPOINT_BASE . '/categories/%d';

    private static function get(string $url): \stdClass|array
    {
        $postsRemote = file_get_contents($url);
        $response    = array();

        if (false !== $postsRemote) {
            $response = json_decode($postsRemote);
        }

        return $response;
    }

    public static function getPost(int $postID): \stdClass
    {
        $post = self::get(sprintf(self::ENDPOINT_POST, $postID));

        return $post;
    }

    public static function getPosts(): array
    {
        $posts = self::get(self::ENDPOINT_POSTS);

        return $posts;
    }

    public static function getMedia(int $mediaID): \stdClass
    {
        $media = self::get(sprintf(self::ENDPOINT_MEDIA, $mediaID));

        return $media;
    }

    public static function getMediaHTML(int $mediaID): string
    {
        $htmlPicture = '';

        $media      = self::getMedia($mediaID);
        $mediaSizes = (array) $media->media_details->sizes;
        uasort(
            $mediaSizes,
            function ($a, $b) {
                return $a->width <=> $a->width;
            }
        );
        $mediaSizes = (object) $mediaSizes;

        ob_start();
        ?>
        <picture>
            <?php foreach ($mediaSizes as $size => $image) { ?>
                <source srcset="<?= $image->source_url ?> <?= $image->width ?>w" type="<?= $image->mime_type ?>" media="(max-width: <?= $image->width ?>px)" />
            <?php } ?>

            <img src="<?= $media->source_url; ?>" alt="<?= $media->alt_text; ?>" />
        </picture>
        <?php
        $htmlPicture .= ob_get_clean();

        return $htmlPicture;
    }

    public static function getCategory(int $categoryID): \stdClass
    {
        $category = self::get(sprintf(self::ENDPOINT_CATEGORIES, $categoryID));

        return $category;
    }

    public static function getCategoriesHTML(array $categoryIDs): string
    {
        $categoriesHTML = '';
        $categoriesName = array();

        foreach ($categoryIDs as $categoryID) {
            $category         = self::get(sprintf(self::ENDPOINT_CATEGORIES, $categoryID));
            $categoriesName[] = $category->name;
        }

        $categoriesHTML = implode(', ', $categoriesName);

        return $categoriesHTML;
    }
}
