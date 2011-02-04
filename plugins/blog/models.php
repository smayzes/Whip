<?php
/**
*   Models for module: blog
*
*/
    class BlogPost extends WhipModel {
    	public static $_pk          = 'id';
        public static $_table       = 'blog_post';
        public static $_fields      = array(
            'id',
            'date_posted',
            'user',
            'title',
            'content',
            'permalink',
        );
    }
    class BlogPostImage extends WhipModel {
    	public static $_pk          = 'id';
        public static $_table       = 'blog_post_image';
        public static $_fields      = array(
            'id',
            'blog_post_id',
            'width',
            'height',
            'title',
        );
    }
    class BlogComment extends WhipModel {
    	public static $_pk          = 'id';
        public static $_table       = 'blog_comment';
        public static $_fields      = array(
            'id',
            'blog_post_id',
            'date_posted',
            'name',
            'email',
            'content',
        );
    }
    