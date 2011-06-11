<?php
/**
*   Models for module: Dropbox
*
*/
    class DropboxFile extends WhipModel {
    	public static $_pk          = 'id';
        public static $_table       = 'dropbox_file';
        public static $_fields      = array(
            'revision',     //  int
            'has_thumb',    //  bool
            'size',         //  int (bytes)
            'modified',     //  timestamp
            'is_dir',       //  bool
            'is_image',     //  bool
            'icon',         //  string
            'mime_type',    //  string
            'human_size',   //  string
            'path',         //  string
            'filename',     //  string
        );
    }
    