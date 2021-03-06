<?php
/**
 * Whip template site
 * 
 * models.php
 *
 */

/**
 * User model.
 * 
 */
    class User extends WhipModel {
        public static $_pk = 'id';
        public static $_table = 'user';
        public static $_fields = array(
            'id',
            'username',
            'password',
            'email',
            'level',
        );
    }   //  User
    