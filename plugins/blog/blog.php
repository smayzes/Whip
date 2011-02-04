<?php
//  Include models for this plugin
    include 'models.php';

/**
 * Blog plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Blog extends WhipPlugin {
    /**
     * get_latest_posts function.
     *
     * Get the latest blog posts.
     * 
     * @access public
     * @param int $limit. (default: 10)
     * @param int $offset. (default: 0)
     * @return void
     */
    public function get_latest_posts($limit=10, $offset=0) {
        $posts      = Whip::Db()->get_all(
            'BlogPost',
            Whip::Query()
                ->order_by('date_posted', 'DESC')
                ->limit($limit)
                ->offset($offset)
        );
        return $posts;
    }   //  get_latest_posts
    
    /**
     * get_post function.
     *
     * Get a blog post by id.
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function get_post($id) {
        return Whip::Db()->get_one('BlogPost', $id);
    }   //  get_post
    
    /**
     * create_post function.
     *
     * Return a new blank blog post
     * 
     * @access public
     * @return void
     */
    public function create_post() {
        return new BlogPost();
    }   //  create_post
        
    /**
     * upload_image function.
     *
     * Upload an image.
     * 
     * @access public
     * @param mixed $uploaded_file
     * @param int $post_id. (default: 0)
     * @param int $jpeg_quality. (default: 80)
     * @return void
     */
    /*
    **  TODO:   Image module needs to be translated from the old Whip framework
    **          before this code is put live
    **
    public function upload_image($uploaded_file, $post_id=0, $jpeg_quality=80) {
        global $config;
        if ($uploaded_file['error']==0 && is_uploaded_file($uploaded_file['tmp_name'])) {
        #   Read uploaded file
            $image                  = $this->whip->image();
            if ($image->load($uploaded_file['tmp_name']) !== false) {
            #   Resize (full)
                $image->resize(
                    $config['blog']['image']['full']['x'],
                    $config['blog']['image']['full']['y'],
                    IMAGE_RESIZE_FIT
                );
            #   Create database record
                $dimensions             = $image->getdimensions();
                $blogpostimage          = new BlogPostImage();
                $blogpostimage->blog_post_id    = $post_id;
                $blogpostimage->width   = $dimensions['x'];
                $blogpostimage->height  = $dimensions['y'];
                $blogpostimage->title   = $uploaded_file['name'];
                $this->whip->db->save($blogpostimage);
            #   Save (full)
                $image->jpg($config['blog']['image']['path'].$blogpostimage->id.'_f.jpg', $jpeg_quality);
            #   Resize (medium)
                $image->resize(
                    $config['blog']['image']['medium']['x'],
                    $config['blog']['image']['medium']['y'],
                    IMAGE_RESIZE_FILL
                );
            #   Save (medium)
                $image->jpg($config['blog']['image']['path'].$blogpostimage->id.'_m.jpg', $jpeg_quality);
            #   Resize (small)
                $image->resize(
                    $config['blog']['image']['small']['x'],
                    $config['blog']['image']['small']['y'],
                    IMAGE_RESIZE_FILL
                );
            #   Save (small)
                $image->jpg($config['blog']['image']['path'].$blogpostimage->id.'_s.jpg', $jpeg_quality);
            
            #   Return image object
                return $blogpostimage;
            }
            else {
                return false;
            }   //  if image load
        }
        else {
            return false;
        }   //  if image
    }   //  upload_image
    
    */
    
/*
    Get image by id
*/
    public function get_image($image_id) {
        return Whip::Db()->get_one('BlogPostImage', $image_id);
    }   //  get_image
    
/*
    Get images for post
*/
    public function get_post_images(BlogPost $post) {
        return Whip::Db()->get_all(
            'BlogPostImage',
            Whip::Query()
                ->where('blog_post_id', $post->id)
        );
    }   //  get_post_images

/*
    Attach image to post
*/
    public function attach_image(BlogPostImage $image, BlogPost $post) {
        $image->blog_post_id = $post->id;
        $image->save();
    }   //  attach_image

/*
    Delete an image
*/
    public function delete_image(BlogPostImage $image) {
    #   Delete from database
        $this->whip->db->execute(
            'DELETE FROM blog_post_image WHERE id='.$image->id
        );
    #   Delete full
        $filename               = $config['blog']['image']['path'].$image->id.'_f.jpg';
        if (file_exists($filename)) unlink($filename);
    #   Delete medium
        $filename               = $config['blog']['image']['path'].$image->id.'_m.jpg';
        if (file_exists($filename)) unlink($filename);
    #   Delete small
        $filename               = $config['blog']['image']['path'].$image->id.'_s.jpg';
        if (file_exists($filename)) unlink($filename);
        return true;
    }   //  delete_image        
    
        
    
}   //  Blog plugin

