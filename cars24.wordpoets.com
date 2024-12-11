server {


    server_name cars24.wordpoets.com www.cars24.wordpoets.com;


    access_log /var/log/nginx/cars24.wordpoets.com.access.log rt_cache;
    error_log /var/log/nginx/cars24.wordpoets.com.error.log;
    # HTTP Authentication on port 22222
    #include common/acl.conf;

    root /var/www/cars24.wordpoets.com/htdocs;

    index index.php index.html index.htm;


    include common/wpfc-php82.conf;

    include common/wpcommon-php82.conf;
    include common/locations-wo.conf;
    include /var/www/cars24.wordpoets.com/conf/nginx/*.conf;
