# FamShare

FamShare is a free alternative to Dropbox, Google Drive or similar file sharing apps. 

It is a file sharing website that you can put on your own web-server so that you, and not Google own your data. 
For example, if you don't want Google or Apple to have all your pictures, you can put your pictures on a computer or 
Raspberry-Pi and host them through FamShare.

![](https://raw.githubusercontent.com/jundl77/FamShare/gh-pages/images/famshare-filehub.png)

You can configure FamShare to fit your own needs, and it works out of the box. Although it is not perfect,
(maybe you can help change that ;D ), it is password protected and easy to use. Great for the family!

## Features
* Offers entire file system infrastructure 
* Password protected
* On click dowload
* Delete files
* Add files through drag and drop or standard dialog
* Limit allowed file types
* Resumable uploads
* Pictures and videos can be viewed directly from within the website

Max upload size is configurable - though it is 10GB by default. 

## Set Up

Download the latest release and put it in the root directory of your web server.

You also need to install PHP-GD if it is not already installed, as FamShare requires it to run properly.

At the end of this page you can find a detailed tutorial on how to set up FamShare with [Nginx](https://en.wikipedia.org/wiki/Nginx) and PHP on a [Raspberry Pi](https://www.raspberrypi.org/) and connect it to an external hard drive.  

## Configure
Below are a couple things you can easily configure yourself through configuration files. Of course you can also edit the code directly to achieve more personalization.

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
      upload_data_thumb => "/directory/where/thumbnails/are/saved/" (Again, make sure the slash is there) 
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
 To have a thumbnail for your new file type, add  ``` my_file_type_extension.png ```  to  ``` /images/file_icons/  ``` and the thumbnail will be loaded automatically.
 
 ---

#### Change Max File Upload Size

1. Go to  ``` /config/server/server_config.php  ``` and change:

 ```php
    // Max file size allowed by script
    "max_size_byte_script" => 10737418240, // in bytes
  ``` 
  
2. Go to   ```  /config/client/client_config.js  ```  and change    ```  maxFilesize: 10000   ``` to your new size (in Megabytes).

## Set up FamShare on a Raspberry Pi

Although this tutorial focuses on the Raspberry Pi, it is essentially the same on any Linux system. 

First off, the assumption is that your Raspberry Pi is set-up and connected to the internet with a static IP and that port-forwarding is working. If you cannot port-forward, then unfortunetaly you cannot host a server at your home.

**0. SSH into your Raspberry Pi**

**1. Update your Raspberry PI**

     sudo apt-get update  
     sudo apt-get upgrade

**2. Install Nginx**

     sudo apt-get install nginx
 
   Start Nginx with
 
     sudo service nginx start
 
   and visit the IP of your Raspberry Pi to see if it is working. You should see the Nginx welcome page. 
   
**3. Install PHP**

    sudo apt-get install php5-fpm
    
**4. Configure PHP**

    sudo nano /etc/php5/fpm/php.ini
    
 Find    ```  cgi.fix_pathinfo   ```,  uncomment it and set it to 0. **Important!**

**5. Configure Nginx**

    sudo nano /etc/nginx/nginx.conf
    
 Inside the http section add: 
 
     # set client body size to 10M (Much bigger files can be uploaded because of chunked uploading) 
     client_max_body_size 10M;  
     
 Then do:
 
     sudo nano /etc/nginx/sites-available/default
     
 Find the following lines:
 
     root /usr/share/nginx/www;
     index index.html index.htm;

And change it to:

     root /usr/share/nginx/www;
     index index.php index.html index.htm;
     
Then find: 

     # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
     #
     # location ~ \.php$ {
     
And remove the # in the lines below it to:

    location ~ \.php$ {
       fastcgi_split_path_info ^(.+\.php)(/.+)$;
       # NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini
    
       # With php5-cgi alone:
       # fastcgi_pass 127.0.0.1:9000;
       # With php5-fpm:
       fastcgi_pass unix:/var/run/php5-fpm.sock;
       fastcgi_index index.php;
       include fastcgi_params;
    }

**6. Install PHP-GD**

    sudo apt-get install php5-gd
    
**7. Set up FamSahre**

Download the latest release of FamShare, extract it, and put all the content (not the build folder, but the content in the build folder) in   ```/usr/share/nginx/www ``` using [FileZilla](https://filezilla-project.org/) or any other FTP client.

You should give the folder permissions:

    sudo chmod -R 775 /usr/share/nginx/www

To configure FamShare, go to the *Configure* section.

Everything should now be up and running! To add an external hard drive to the Raspberry Pi so that you can store an unlimited amount of data read on.

**8. Set up an External Hard Drive**

Connect your hard drive to the Raspberry Pi and run

    sudo blkid

to see if it is found.

Find the partiton name of the hard drive:

    sudo fdisk –l

The partition should start with /dev (eg. /dev/sda1).

Format the disk to ext4 if it is not already in that format:

    sudo mkfs.ext4 /dev/MyPartitionNameFromAbove

Mount the disk to the /mnt directory:

    sudo mount /dev/sda1 /mnt
 
 Give permission to that folder:
 
     sudo chmod 775 /mnt
 
 Then to mount the drive at boot:
 
     sudo nano /etc/fstab
 
 And add: 
 
     /dev/MyPartitionNameFromAbove     /mnt     ext4     defaults     0     0
 
 You should now be ready to go!
 
 For any remaining questions, feel free to contact me!

## License

The MIT License (MIT)

Copyright (c) 2015, Julian Brendl

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
