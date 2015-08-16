# FamShare

FamShare is a free alternative to Dropbox, Google Drive or similar file sharing apps. 

It is nothing but a file sharing website that you can put on your own web-server so that you, and not Google own your data. 
For example, if you don't want Google or Apple to have all your pictures, you can put your pictures on a computer or 
raspberry-pi and host them through FamShare.

![](https://raw.githubusercontent.com/jundl77/FamShare/doc/doc/famshare-filhub.png)

You can configure FamShare to fit your own needs, and it works out of the box. Although it is not the fastest,
(maybe you can help change that ;D ), it is password protected and easy to use. Great for the family!

## Features
* Offers entire file system infrastructure 
* Password protected
* On click dowload
* Delete files
* Add files through drag and drop or standard dialog
* Limit allowed file types

Max upload size is configurable - though it is 10GB by default. 

## Configure
Below are a couple things you can easily configure yourself through configuration files. Of course you can also edit the code directly to achieve more personalization.

---

#### Change Name

Change the name of website from FamShare to YourLastNameShare (eg. SmithShare):
 
  ```
    1. Go to /config/client/client_config.js
    2. Set:
      enabled: true  
      name: Smith 
  ```
  
---

#### Change Upload Directory

Change the directories where uploaded files are saved:

 ``` 
    1. Go to /config/server/server_config.php
    2. Set:
      upload_data => "/full/path/to/upload/directory/" (It is important that the last slash is there)
      upload_data_thumb => "/directory/where/thumbnails/are/saved" (Again, make sure the slash is there) 
  ``` 
  After you changed directories, you might have to  ``` chmod  ``` that directory
  
---

#### Change Password

 ``` 
    1. Go to /config/server/server_config.php
    2. Set:
      "password" => "My new password!"
  ``` 

---

#### Change Allowed File Types

 ``` 
  1. Got to /config/server/server_config.php
  2. Add your desired extension to the array:
    "legal_exts" => array(
          'jpg', 'jpeg', 'gif', 'png', 'wbmp', 'txt', 'mp3', 'mp4', 'mpg', 'mov', 'm4v', 'pdf', 'doc', 'docx',
          'ppt', 'wmv'
      )
 ``` 
 To have a thumbnail for your new file type, add  ``` my_file_type_extension.png ```  to  ``` /images/file_icons/  ``` and the
 thumbnail will be load automatically
 
 ---

#### Change Max File Upload Size

1. This is a bit more complicated, start off by going to  ``` /config/server/server_config.php  ``` and changing:

 ```php
    // Max file size allowed by script
    "max_size_byte_script" => 10737418240, // in bytes

    // Max file size allowed by the php engine
    "upload_max_filesize" => "10000M",

    // The max size of a request that can be posted to a php script, should be bigger than upload_max_filesize
    "post_max_size" => "11000M",

    // Max input time in seconds allowed for script to parse data
    "max_input_time" => 36000,

    // Max time in seconds allowed for script to run before it is terminated by the parser
    "max_execution_time" => 36000
  ``` 

  Note that you should give the script enough time to process larger files (here 10hrs for 10GB max :P )

2. Then go into your   ``` php.ini   ```  file and change the same variables as above to values you just set

3. Go to   ```  /config/client/client_config.js  ```  and change    ```  maxFilesize: 10000   ``` to your new size (in Megabytes)

4. If you are using Nginx as a web-server, you also have to change    ```client_max_body_size 10000M;   ``` in
   ```  /etc/nginx/nginx.conf   ```  on the server

---
