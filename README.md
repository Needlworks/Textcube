Textcube: Brand Yourself - Personalized web publishing platform
===============================================================

## DESCRIPTION

Textcube is an opensource tool to archive and share the experiences, ideas, opinions and thoughts.

Supports import/export individual data via XML compatible with other solutions in 'Tattertools Project'

* Strong support of non-latin compatibility including Korean/Japanese/Chinese
* Supports various installation environments (webservers,databases and languages)
* Provides and extensible plugin and skin architecture
* Expandable from individual blog to blog service platform.
* Supports easy backup and restore via TTXML format, which is supported by various platforms of 'Project Tattertools.'

## HISTORY

Textcube is based on online publishing platform 'Tattertools,' started by JH in 2004, developed by TNC in 2005 and GPLized in 2006. Tatter Network Foundation (TNF) developed Tattertools with TNC from Apr. 2006. Needlworks/TNF was dedicated to Tattertools' development from Nov. 2006, and started developing 'Project S2' as its successor. 'Textcube' was named by YJ Park, and made its debut in Aug. 2007.

## REQUIREMENTS (CURRENT VERSION)
Textcube supports various environments. However, you need at least one webserver supporting PHP environments, one database engine

* Web servers (Need at least one environment)
 * Apache 2.1 or above
  * fancyURL support with mod_rewrite module
 * Nginx 1.1
 * IIS 5.0 or above
  * with ISAPI Rewrite Filter
* Language
 * PHP 5.2 or above
  * with iconv / gd module
* Database Management System (Need at least one environment)
 * MySQL > 5.0 / MariaDB 5.1 with UTF-8 character set and collation setting
 * Cubrid R2008 or above
 * PostgreSQL 8.3 or above
 * Sqlite 3 or above


For massive service / Heavy load environments

 * APC (Alternative PHP Cache) pecl package with PHP PEAR
 * XCache
 * memcached module with PHP memcached

are strongly recommended.

## REQUIREMENTS (OLD VERSIONS)

* Web servers (Need at least one environment)
 * Apache 1.3 or above
  * fancyURL support with mod_rewrite module
* Language
 * (Till Textcube 1.7) PHP 4.3~5.1
  * with iconv / gd module
* Database Management System (Need at least one environment)
 * (Till Textcube 1.7) MySQL > 4.1 / MariaDB > 5 (lower version with UTF-8 emulation routine in Textcube)

## INSTALLATION

Before you start, you need to

* know the port / username / password of your database
* have the permission to modify webserver configuration.

Uncompress the downloaded file, locate them to the web-accessible location. Assume that the textcube location is /var/www/textcube.

This is apache setting.

    <VirtualHost *:80>
        ServerName www.example.org
        ServerAlias www.example.org
        ServerAdmin admin@example.org
        DocumentRoot /var/www/textcube/
        <Directory /var/www/textcube>
            AllowOverride FileInfo
            Order allow,deny
            allow from all
        </Directory>
    </VirtualHost>

This is nginx setting.

    server {
       listen  80;
       server_name example.org *.example.org;
       root    /var/www/textcube;

       location /  {
           root    /var/www/textcube;
           set $rewrite_base '';
           if (!-f $request_filename) {
               rewrite ^(thumbnail)/([0-9]+/.+)$ cache/$1/$2;
           }
           if ($request_filename ~* ^(cache)+/+(.+[^/])\.(cache|xml|txt|log)$) {
               return 403;
           }
           if (-d $request_filename) {
               rewrite ^(.+[^/])$ $1/;
           }
           rewrite  ^(.*)$ $rewrite_base/rewrite.php last;
       }

       location ~ \.php$ {
           fastcgi_pass   127.0.0.1:9000;
           fastcgi_index  index.php;
           fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
           fastcgi_param  QUERY_STRING     $query_string;
           fastcgi_param  REQUEST_METHOD   $request_method;
           fastcgi_param  CONTENT_TYPE     $content_type;
           fastcgi_param  CONTENT_LENGTH   $content_length;
           include fastcgi_params;
       }
       location ~ /\.ht {
           deny all;
       }
    }

If the accessible URL is http://www.example.org, run the installation program by accessing http://www.example.org/setup.php. Follow the setup procedure.

* [Installation (Korean)](http://help.tattertools.com/ko/index.php?title=Getting_Started)

## RUNNING

## DOCUMENTATION

### USERS
* [Shortcuts](https://github.com/Needlworks/Textcube/wiki/shortCutList)


### SPECIFICATIONS AND STRUCTURES

* [Requirements](https://github.com/Needlworks/Textcube/wiki/requirements)

* [Upgrade instruction from 1.7 to 1.8 or higher version](https://github.com/Needlworks/Textcube/wiki/attentionOnInstallation)
* [Configurable options](https://github.com/Needlworks/Textcube/wiki/configOptions)

* [Skin](https://github.com/Needlworks/Textcube/wiki/SkinDocs)
* [Skin replacer list](https://github.com/Needlworks/Textcube/wiki/replacer)
* [Predefined styles](https://github.com/Needlworks/Textcube/wiki/skinpredefined)
* [Skin information file specification](https://github.com/Needlworks/Textcube/wiki/skin/index_xml)
* [Tattertools/Textcube online manual wiki](http://help.tattertools.com)

* [Plugins](https://github.com/Needlworks/Textcube/wiki/PluginDocs)
* [Plugin Creation](https://github.com/Needlworks/Textcube/wiki/PluginIntroduction)
* [Plugin Specification](https://github.com/Needlworks/Textcube/wiki/pluginSpec)
* [Plugin Event Listeners](https://github.com/Needlworks/Textcube/wiki/pluginEvents)

* [TTXML format specification](https://github.com/Needlworks/Textcube/wiki/TTXML)
* [WPI package creation](https://github.com/Needlworks/Textcube/wiki/WPI)

### DEVELOPMENT
* [Ticketing process](https://github.com/Needlworks/Textcube/wiki/ticketProcess)
* [Coding guideline](https://github.com/Needlworks/Textcube/wiki/codingGuideline)
* [Commiter/Reporter List](https://github.com/Needlworks/Textcube/wiki/contributorList)

* [Developing references](https://github.com/Needlworks/Textcube/wiki/devReference)
* [Textcube 1.8 changes for Plugin Developers](http://docs.google.com/View?id=dgc24tzr_136ckbg4ngn)
* [Textcube 1.8 changes for Skin Designers](http://docs.google.com/View?id=dgc24tzr_138hhfbmwdg)
* [Textcube 1.8 changes for Server administrators and service hosts / maintainers](http://docs.google.com/View?id=dgc24tzr_137gr9xpdfb)
* [Textcube 1.8 changes for coding geeks](http://docs.google.com/View?id=dgc24tzr_140c9wz6nc5)

## EXTERNAL LINKS

* [Textcube notice blog](http://notice.textcube.org/ko)
* [Needlworks](http://www.needlworks.org)
* [Needlworks Blog](http://blog.needlworks.org)
* [Tatter Network Foundation forum](http://forum.tattersite.com/ko)
