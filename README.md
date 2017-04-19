# ATD Skinwalker for Wordpress #

A Wordpress plugin that allows dynamic resizing/cropping of images that have been uploaded to the default wordpress 'uploads' folder.

### Installation ###

#### Wordpress Admin ####

Clone the repo to wp-content/plugins/atd_skinwalker

Activate the plugin from the wordpress Plugins page

Visit the settings page by navigating to **Settings >> ATD Skinwalker** to verify that everything is correctly installed.

#### NGINX ####

Open up your vhost file and enter this in:

```
location /wp-content/uploads {
	rewrite ^/(.+)/(.+)/(.+)\.(jpg|jpeg|png|gif|bmp)$ /wp-content/plugins/atd_skinwalker/index.php last;
}
```

#### Manual ####

Clone the repo to wp-content/plugins/atd_skinwalker

```
hg clone ssh://hg@bitbucket.org/airtightdesign/atd_skinwalker

```
Put .htaccess file in uploads folder (make sure mod_rewrite is enabled first!)
```
cp wp-content/plugins/atd_skinwalker/htaccess wp-content/uploads/.htaccess
```
Create cache directory
```
mkdir wp-content/plugins/atd_skinwalker/cache;
```
Set cache directory ownership
```
chgrp www-data wp-content/plugins/atd_skinwalker/cache;
```
Set cache directory permissions
```
chmod 775 wp-content/plugins/atd_skinwalker/cache;
chmod g+s wp-content/plugins/atd_skinwalker/cache;
```

### Clearing the Cache ###

#### Wordpress Admin ####
Visit **Settings >> ATD Skinwalker**

#### Manual ####
```
rm -rf wp-content/plugins/atd_skinwalker/cache/*
```

### Test It ###

Assuming you have uploaded the file lena.jpg through the wordpress admin, it should now exist somewhere in the up to /wp-content/uploads/YYYY/MM/lena.jpg.  Open the image in a fresh tab and try adding the following string to the end of the url: ?a=crop&w=100&h=100&x=10&y=10

### Available Parameters ###

* a - the 'action' to perform (resize|crop - defaults to 'resize')
* r - the type of resize (contain|widen|heighten|cover)
* w - the width
* h - the height
* x - the x offset
* y - the y offset

#### Image Resizing ####

Contain an image within a bounding box
```
/wp-content/uploads/YYYY/MM/lena.jpg?r=contain&w=200&h=200
```
![lena.jpg](https://bitbucket.org/repo/xdMGXB/images/2422800789-lena.jpg)
![arrow.jpg](https://bitbucket.org/repo/xdMGXB/images/3681154042-arrow.jpg)
![lena-contain-200x200.jpg](https://bitbucket.org/repo/xdMGXB/images/1202344600-lena-contain-200x200.jpg)


Resize an image by specifying the width, preserving aspect ratio
```
/wp-content/uploads/YYYY/MM/lena.jpg?r=widen&w=200
```
![lena.jpg](https://bitbucket.org/repo/xdMGXB/images/2422800789-lena.jpg)
![arrow.jpg](https://bitbucket.org/repo/xdMGXB/images/3681154042-arrow.jpg)
![lena-widen-200.jpg](https://bitbucket.org/repo/xdMGXB/images/3705539239-lena-widen-200.jpg)


Resize an image by specifying the height, preserving aspect ratio
```
/wp-content/uploads/YYYY/MM/lena.jpg?r=heighten&h=200
```
![lena.jpg](https://bitbucket.org/repo/xdMGXB/images/2422800789-lena.jpg)
![arrow.jpg](https://bitbucket.org/repo/xdMGXB/images/3681154042-arrow.jpg)
![lena-heighten-200.jpg](https://bitbucket.org/repo/xdMGXB/images/1935684871-lena-heighten-200.jpg)


Resize an image to cover the provided box dimensions.  This can clip the image.
```
http://thesite.com/wp-content/uploads/YYYY/MM/lena.jpg?r=cover&w=100&h=200
```
![lena.jpg](https://bitbucket.org/repo/xdMGXB/images/2422800789-lena.jpg)
![arrow.jpg](https://bitbucket.org/repo/xdMGXB/images/3681154042-arrow.jpg)
![lena-cover-100x200.jpg](https://bitbucket.org/repo/xdMGXB/images/3006622458-lena-cover-100x200.jpg)



#### Image Cropping ####

The following will crop the image to 100x100 pixels, starting at position 10,15
```
/wp-content/uploads/YYYY/MM/lena.jpg?a=crop&w=100&h=100&x=100&y=100
```
![lena.jpg](https://bitbucket.org/repo/xdMGXB/images/2422800789-lena.jpg)
![arrow.jpg](https://bitbucket.org/repo/xdMGXB/images/3681154042-arrow.jpg)
![lena-crop-100x100-100x100.jpg](https://bitbucket.org/repo/xdMGXB/images/1813979264-lena-crop-100x100-100x100.jpg)


### Who do I talk to? ###

wjbrown@airtightdesign.com