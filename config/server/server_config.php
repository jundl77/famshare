<?php

return array(
    "root_upload_dirs" => array(
        // Where to save the uploaded data
        "upload_data" => "./uploadData/",

        // In case the data is an image, store its thumbnail here in order to display it
        "upload_data_thumb" => "./uploadDataThumb/"
    ),


    // All allowed file types to upload
    "legal_exts" => array(
        'jpg', 'jpeg', 'gif', 'png', 'wbmp', 'txt', 'mp3', 'mp4', 'mpg', 'mov', 'm4v', 'pdf', 'doc', 'docx',
        'ppt', 'wmv'
    ),


    // Password to enter (this is not very secure, please do not use an important password)
    "password" => "test!",


    // Max file size allowed by script
    "max_size_byte_script" => 10737418240, // in bytes (to power of base 2) (currently at 10GB)


    /*
     * ================================================================================================
     * IMPORTANT:
     * The below values have to be changed in the php.ini file as well (same name)
     * ================================================================================================
     */
    // Max file size allowed by the php engine
    "upload_max_filesize" => "10000M",   // not actual # of megabyte (to power of base 10)

    // The max size of a request that can be posted to a php script, should be bigger than upload_max_filesize
    "post_max_size" => "11000M",    // not actual # of megabyte (to power of base 10)

    // Max input time in seconds allowed for script to parse data
    "max_input_time" => 36000,

    // Max time in seconds allowed for script to run before it is terminated by the parser
    "max_execution_time" => 36000
);