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

* Change name of website from FamShare to YourLastNameShare (eg. SmithShare):
 
  ```
    1. Go to /config/client/client_config.js
    2. Set:
      enabled: true  
      name: Smith 
  ```
  
---

* Change upload directories on the server (where uploaded files are saved):

 ``` 
    1. Go to /config/client/server_config.js
    2. Set:
      upload_data => "/full/path/to/upload/directory/" (It is important that the last slash is there)
      upload_data_thumb => "/directory/where/thumbnails/are/saved" (Again, make sure the slash is there) 
  ``` 
  After you changed directories, you might have to  ``` chmod  ``` that directory
  
---

* Change password:

 ``` 
    1. Go to /config/client/server_config.js
    2. Set:
      "password" => "My new password!"
  ``` 

---

* Change allowed file types:

 ``` 
  1. Got to /config/client/server_config.js
  2. Add your desired extension to the array:
    "legal_exts" => array(
          'jpg', 'jpeg', 'gif', 'png', 'wbmp', 'txt', 'mp3', 'mp4', 'mpg', 'mov', 'm4v', 'pdf', 'doc', 'docx',
          'ppt', 'wmv'
      )
 ``` 
 To have a thumbnail for your new file type, add  ``` my_file_type_extension.png ```  to  ``` /images/file_icons/  ``` and the
 thumbnail will be load automatically
