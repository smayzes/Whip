<?php

/**
 * Mail class.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
require_once(Whip::real_path(__DIR__).'inlineimage.php');

define('ERROR_FILE_NOT_FOUND', 'Image not found.');
define('CR', "\r");
define('LF', "\n");
define('CRLF', "\r\n");
    
class Mail extends WhipPlugin {
	//global $config;
	
//  Cross-instance
	protected static $_require = array('InlineImage', 'Db', 'Query');           //  array: names of plugins required to run this plugin

    //private $name 		= $config['email']['name'];
    //private $email  		= $config['email']['email'];
    private $to             = array();
    private $cc             = array();
    private $bcc            = array();
    private $attachment     = array();
    private $inline_image   = array();  //  inline images
    private $from;
    private $subject;
    private $body_text;
    private $body_html;
    
    /**
     * populate the from variable.
     * 
     * @access public
     * @param mixed $from
     * @return void
     */
    public function from($from) {
        $this->from = $from;
        return $this;
    }
    
    /**
     * populate the name variable.
     * 
     * @access public
     * @param mixed $name
     * @return void
     */
    public function name($name) {
        $this->name[] = $name;
        return $this;
    }
    
    /**
     * populate the to variable.
     * 
     * @access public
     * @param mixed $to
     * @return void
     */
    public function to($to) {
        $this->to[] = $to;
        return $this;
    }
    
    /**
     * populate the cc variable.
     * 
     * @access public
     * @param mixed $cc
     * @return void
     */
    public function cc($cc) {
        $this->cc[] = $cc;
        return $this;
    }
    
    /**
     * populate the bcc variable.
     * 
     * @access public
     * @param mixed $bcc
     * @return void
     */
    public function bcc($bcc) {
        $this->bcc[] = $bcc;
        return $this;
    }
    
    /**
     * populate the subject variable.
     * 
     * @access public
     * @param mixed $subject
     * @return void
     */
    public function subject($subject) {
        $this->subject = $subject;
        return $this;
    }
	
	/**
     * populate the text variable.
     * 
     * @access public
     * @param mixed $text
     * @return void
     */
    public function text($text) {
        $this->body_text    = $text;
        return $this;
    }
    
    /**
     * populate the attache variable.
     * 
     * @access public
     * @param mixed $filename
     * @return void
     */
    public function attach($filename) {
    //   Check if file exists
        if (!file_exists($filename)) {
            throw new WhipModelException(ERROR_FILE_NOT_FOUND);
            return false;
        }
    //   Get mime type
        $mime_type          = $this->whip->mime->get($filename);
    //   Add attachment
        $this->attachment[] =
            'Content-Type: '.$mime_type.';'.LF.
            ' name="'.basename($filename).'"'.LF.
            'Content-Disposition: attachment;'.LF.
            ' filename="'.basename($filename).'"'.LF.
            'Content-Transfer-Encoding: base64'.LF.LF.
            chunk_split(base64_encode(file_get_contents($filename)));
            
        return $this;
    }   //  function attach

	/**
     * populate the html variable.
     * 
     * @access public
     * @param mixed $html
     * @return void
     */
    public function html($html) {
        $this->body_html    = $html;
        return $this;
    }
    
    /**
     * populate the inline_image variable
     * 
     * @access public
     * @param mixed $filename
     * @return void
     */
    public function inline_image($filename) {
    //   Check if file exists
        if (!file_exists($filename)) {
            throw new WhipModelException(ERROR_FILE_NOT_FOUND);
            return false;
        }
    //   Get mime type
        $mime_type          = $this->whip->mime->get($filename);
    //   Generate unique CID
        $unique_id          = md5(uniqid());
        $cid                = 'WhipMail_c'.$unique_id;
    //   Add attachment
        $inline_image       = new InlineImage();
        $inline_image->filename = basename($filename);
        $inline_image->cid      = $cid;
        $inline_image->data     = 
            'Content-Type: '.$mime_type.';'.LF.
            ' name="'.$inline_image->filename.'"'.LF.
            'Content-ID: <'.$cid.'>'.LF.
            'Content-Transfer-Encoding: base64'.LF.LF.
            chunk_split(base64_encode(file_get_contents($filename)));
        $this->inline_image[]   = $inline_image;
        return $this;
    }   //  function inline_image
    
    /**
     * send the email with all the class variables that are set.
     * 
     * @access public
     * @return void
     */
    public function send() {
    //   Generate boundaries
        $unique_id  = md5(uniqid());
        $bnd_mixed  = 'WhipMail_m'.$unique_id;    //  multipart/mixed boundary
        $bnd_alt    = 'WhipMail_a'.$unique_id;    //  multipart/alternative boundary
        $bnd_rel    = 'WhipMail_r'.$unique_id;    //  related (inline images)
    /*
    **  Header
    */
        $header     = 'From: '.$this->from.LF;
        if (count($this->cc)) {
          $header      .= 'Cc: '.implode(',', $this->cc).LF;
        }
        if (count($this->bcc)) {
          $header      .= 'Bcc: '.implode(',', $this->bcc).LF;
        }
        $header    .= 'MIME-Version: 1.0'.LF;
        $header    .= 'Content-Type: multipart/mixed; boundary='.$bnd_mixed;

    /*
    **  Message
    */
        $message    = 'Content-Type: multipart/alternative; boundary='.$bnd_alt.LF.LF;
    //   Plain text body
        if (strlen($this->body_text)) {
            $message   .=
                '--'.$bnd_alt.LF.
                'Content-Type: text/plain; charset=ISO-8859-1'.LF.
                'Content-Transfer-Encoding: 8bit'.LF.
                LF.
                $this->body_text.LF.
                LF;
        }
    //   HTML body
        if (strlen($this->body_html)) {
            if (count($this->inline_image)) {
            //   HTML with inline images
            //   Replace inline images before creating the body
                $body_html  = $this->body_html;
                foreach($this->inline_image as &$ii) {
                    $body_html  = str_replace($ii->filename, 'cid:'.$ii->cid, $body_html);
                }
            //   HTML
                $message   .=
                    '--'.$bnd_alt.LF.
                    'Content-Type: multipart/related; boundary="'.$bnd_rel.'"'.LF.
                    LF.
                    '--'.$bnd_rel.LF.
                    'Content-Type: text/html; charset=ISO-8859-1'.LF.
                    'Content-Transfer-Encoding: 8bit'.LF.
                    LF.
                    $body_html.LF.
                    LF;
            //   Inline image data
                foreach($this->inline_image as &$ii) {
                    $message    .=
                        '--'.$bnd_rel.LF.
                        $ii->data.LF;
                }
                $message    .= '--'.$bnd_rel.'--'.LF.LF;
                
            }
            else {
            //   Just plain HTML
                $message   .=
                    '--'.$bnd_alt.LF.
                    'Content-Type: text/html; charset=ISO-8859-1'.LF.
                    'Content-Transfer-Encoding: 8bit'.LF.
                    LF.
                    $this->body_html.LF.
                    LF;
            }
        }
        $message   .= '--'.$bnd_alt.'--'.LF.LF;
        
    /*
    **  Wrap the multipart/alternative message (text / html)
    **  in a multipart/mixed envelope, allowing attachments.
    */
        $envelope   = '';
        $envelope   = 'This is a multipart message in MIME format.'.LF.
                      'If your email client does not support this,'.LF.
                      'a plain text version is provided below.'.LF.
                      '--'.$bnd_mixed.LF;
    //   Message
        $envelope  .= $message;
    //   Attachments
        if (count($this->attachment)) {
            foreach($this->attachment as &$attachment) {
                $envelope  .= '--'.$bnd_mixed.LF;
                $envelope  .= $attachment.LF.LF;
            }   //  each attachment
        }   //  if attachments
    //   EOM boundary
        $envelope  .= '--'.$bnd_mixed.'--'.LF;
        
    /*
    **  Send!
    */
        foreach ($this->to as $to) {
            mail($to, $this->subject, $envelope, $header);
        }
        
    }   //  function send
    
}   //  class Mail
