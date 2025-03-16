# Installation for setting up Bodhi

> Instructions are based on the two following specifics, an Ubuntu / Debian based system, using Nginx as the web server. This setup will have the database server running on a seperate server. 
---
Web Server
---
1. Updating the server, starting with a barebones installation of Ubuntu/Debian: 

        sudo apt-get update && sudo apt-get upgrade 

2. Install the following applicaitons:

        sudo apt-get install -y nginx composer npm supervisor php php-mysql mysql-client awscli php7.2-mbstring php7.2-xml redis-server node.js php-redis php7.2-zip 
    
    > Optional: Install Fail2Ban service for protection against DDoS attacks.

        sudo apt-get install fail2ban 

3. NPM installation:

        sudo npm install -g laravel-echo-server noty

4. Nginx configuration:

    Configurations for Nginx will vary on many systems, but this document will provide an example that will require some substitution. Anywhere that $HOST is used, this should be replaced with the hostname of the server, including the FQDN
    a. /etc/sites-available/$HOST

            #
            server {
            listen 443 ssl default_server;
            listen [::]:443 default_server;
            include snippets/snakeoil.conf;
            client_max_body_size 50M;
            ssl on;
            ssl_certificate /etc/nginx/ssl/$HOST.crt;
            ssl_certificate_key /etc/nginx/ssl/$HOST.key;
            ssl_ciphers "AES128+EECDH:AES128+EDH";
            ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
            ssl_prefer_server_ciphers on;
            ssl_session_cache shared:SSL:10m;
            add_header Strict-Transport-Security "max-age=63072000; includeSubDomains";
            add_header X-Content-Type-Options nosniff;
            ssl_stapling on;
            ssl_stapling_verify on;
            resolver_timeout 5s;
            root /var/www/html/bodhi/public;

            # Add index.php to the list if you are using PHP
            index index.php index.html index.htm index.php index.nginx-debian.html;

            server_name $HOST;

            location / {
                # First attempt to serve request as file, then
                # as directory, then fall back to displaying a 404.
                #try_files $uri $uri/ =404;
                try_files $uri $uri/ /index.php?$query_string;

            }
                location ~ \.php$ {
                    include snippets/fastcgi-php.conf;
                    fastcgi_pass unix:/run/php/php7.2-fpm.sock;
                }

                location ~ /\.ht {
                    deny all;
                }
            #the following would go within the server{} block of your web server config
            location /socket.io {
                    proxy_pass http://127.0.0.1:6001; #this assumes Echo and NginX are on the same box
                    proxy_http_version 1.1;
                    proxy_set_header Upgrade $http_upgrade;
                    proxy_set_header Connection "upgrade";
                    proxy_set_header Host $host;
                    proxy_cache_bypass $http_upgrade;
                }
            }
    b. /etc/nginx/ssl/$HOST.crt & /etc/nginx/ssl/$HOST.key 

    This should be a valid SSL certificate that is using the same valid hostname for the system. Below are commands for creating a self signed certificate, and for creating a valid SSL certificate request. While the valid SSL may have a cost, it is much more preferable over a self signed certificate. 
        
    Self Signed: 

            openssl req -x509 -newkey rsa:4096 -keyout $HOST.key -out $HOST.crt -days 365

    CSR Request:

            openssl req -out $HOST.csr -new -newkey rsa:2048 -nodes -keyout $HOST.key

    Once the CSR is submitted to the Registrar, and a valid SSL certificate is generated, it should be saved to the server as /etc/nginx/ssl/$HOST.crt

    c. /var/www/html/bodhi permissions: 
    
    The permissions on the bodhi directory must be particular for them to be written to, and read. Run the following commands below to set the correct permissions: 

            sudo chown -R www-data:www-data /var/www/html/
            sudo chmod 777 -R /var/www/html/
            sudo chmod -R ug+rwx /var/www/html/bodhi/storage /var/www/html/bodhi/bootstrap/cache

5. Supervisor configuration: 
    a. /etc/supervisor/supervisor.conf

        [unix_http_server]
        file = /tmp/supervisor.sock

        [supervisord]
        logfile          = /var/log/supervisord.log
        logfile_maxbytes = 50MB
        logfile_backups  = 10
        loglevel         = info
        pidfile          = /var/tmp/supervisord.pid
        nodaemon         = false
        minfds           = 1024
        minprocs         = 200

        [rpcinterface:supervisor]
        supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

        [supervisorctl]
        serverurl = unix:///tmp/supervisor.sock

        [include]
        files = /etc/supervisor/conf.d/*.conf

        [program:bodhi-echo]
        directory=/var/www/html/bodhi
        process_name=%(program_name)s_%(process_num)02d
        command=/usr/local/bin/laravel-echo-server start
        autostart=true
        autorestart=true
        user=ubuntu
        numprocs=1
        redirect_stderr=true
        stdout_logfile=/var/www/html/bodhi/storage/logs/echo.log

        [program:bodhi-horizon]
        process_name=%(program_name)s
        command=php /var/www/html/bodhi/artisan horizon
        autostart=true
        autorestart=true
        user=ubuntu
        redirect_stderr=true
        stdout_logfile=/var/www/html/bodhi/storage/logs/horizon.log
    > Note: You will want to replace the user in the configuration file, with a valid service account type user
    b. Add service account to www-data group:

            sudo usermod -aG www-data ubuntu
    > Note: Replace the user again here, you may need to reboot the server for Supervisor to be able to write to the /var/www/html/bodhi/storage/logs/echo.log folder 

    c. Start services using Supervisor 

            sudo supervisorctl reread 
            sudo supervisorctl restart all 
    
    d. Setup crontab with user account that runs Supervisor commands 

            crontab -e * * * * * cd /var/www/html/bodhi && php artisan schedule:run >> /dev/null 2>&1

6. Laravel echo server configuration:

    a. Run initialize laravel-echo server in html directory 

            cd /var/www/html/bodhi 
            laravel-echo-server init 

    b. Update /var/www/html/bodhi/laravel-echo-server.json 

        {
            "authHost": "https://$HOST",
            "authEndpoint": "/broadcasting/auth",
            "clients": [],
            "database": "redis",
            "databaseConfig": {
                "redis": {},
                "sqlite": {
                    "databasePath": "/database/laravel-echo-server.sqlite"
                }
            },
            "devMode": true,
            "host": null,
            "port": "6001",
            "protocol": "https",
            "socketio": {},
            "sslCertPath": "/etc/nginx/ssl/$HOST.crt",
            "sslKeyPath": "/etc/nginx/ssl/$HOST.key",
            "sslCertChainPath": "",
            "sslPassphrase": "",
            "subscribers": {
                "http": true,
                "redis": true
            },
            "apiOriginAllow": {
                "allowCors": false,
                "allowOrigin": "",
                "allowMethods": "",
                "allowHeaders": ""
            }
        }
    > Note: This is a a basic example that the initialization will create 
    > Note: Replace $HOST with the server FQDN 

    c. Create /var/www/html/bodhi/.env

        APP_NAME="Bodhi"
        APP_ENV=production
        APP_KEY=base64:zCorxdtXrto79cPS3G8k89OmbyyoD9VCSqqIpJFPe+c=
        APP_DEBUG=false
        APP_URL=https://$HOST

        LOG_CHANNEL=daily

        DB_CONNECTION=mysql
        DB_HOST=$DBSERVER
        DB_PORT=3306
        DB_DATABASE=$DBNAME
        DB_USERNAME=$DBUSERNAME
        DB_PASSWORD='$DBPASS'

        BROADCAST_DRIVER=redis
        CACHE_DRIVER=file
        QUEUE_CONNECTION=redis
        SESSION_DRIVER=file
        SESSION_LIFETIME=120

        REDIS_HOST=127.0.0.1
        REDIS_PASSWORD=null
        REDIS_PORT=6379

        MAIL_DRIVER=smtp
        MAIL_HOST=$MAILSERVER
        MAIL_PORT=587
        MAIL_USERNAME=$EMAILADDRESS
        MAIL_PASSWORD=$EMAILPASS
        MAIL_ENCRYPTION=tls

        MIX_SOCKET_PATH=6001

        PUSHER_APP_ID=
        PUSHER_APP_KEY=
        PUSHER_APP_SECRET=
        PUSHER_APP_CLUSTER=mt1

        MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
        MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

        FILESYSTEM_DRIVER=s3
        AWS_ACCESS_KEY_ID=$AWSACCESSKEY
        AWS_DEFAULT_REGION=$AWSREGION
        AWS_SECRET_ACCESS_KEY=$AWSSECRETKEY
        AWS_BUCKET=$BUCKETNAME
        AWS_URL=s3.$AWSREGION.amazonaws.com

    > Fields to be updated:
    > - $HOST - to be replaced with server FQDN
    > - $DBSERVER - database server name or IP 
    > - $DBNAME - name of the database 
    > - $DBUSERNAME - username for connecting to the database server
    > - $DBPASS - password for connecting to the database server
    > - $MAILSERVER - email server for sending emails out of Bodhi
    > - $EMAILADDRESS - email address to be used for sending mail 
    > - $EMAILPASS - password that corresponds with the email address 
    > - $AWSACCESSKEY - Access key for IAM user that has bucket read/write access 
    > - $AWSREGION - AWS region bucket is located in 
    > - $AWSSECRETKEY - corresponding AWS Secret Key for IAM User 
    > - $BUCKETNAME - AWS that stores data 

7. Composer install dependancies 

        cd /var/www/html/bodhi 
        composer install 


8. Restart Nginx 

    sudo service nginx restart 

> Optional: Install Fail2Ban service for protection against DDoS attacks.

    sudo apt-get install fail2ban 

---
Database Server
---
This guide should install mysql, create a database, and create login credentials for to be used in the application. 

1. Updating the server, starting with a barebones installation of Ubuntu/Debian: 

        sudo apt-get update && sudo apt-get upgrade 

2. Install the following applicaitons:

        sudo apt-get install -y mysql-server 

3. Using a default installation, change the root user to access mysql

        sudo su
        mysql 

4. Create database, and database user from mysql prompt:

        CREATE DATABASE $DBNAME;
        CREATE USER '$DBUSERNAME'@'$HOST' IDENTIFIED BY '$DBPASS';
        GRANT USAGE ON $DBNAME.* TO '$DBUSERNAME'@'$HOST' IDENTIFIED BY '$DBPASS';
        GRANT ALL PRIVILEGES ON $DBNAME.* TO '$DBUSERNAME'@'$HOST';

> Fields to be updated:
> - $HOST - to be replaced with server FQDN
> - $DBSERVER - database server name or IP 
> - $DBNAME - name of the database 
> - $DBUSERNAME - username for connecting to the database server
> - $DBPASS - password for connecting to the database server

5. Update mysql configuration file to listen on IP of server:
> This is needed, otherwise mysql will not accept connections

        sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
        sudo service mysql restart 


---
Checking Status, Troubleshooting:
---

Status:
___

Supervisor Status: 

    sudo supervisorctl status 

View number of socket connections: 

    watch -n 5 " netstat -an | grep :6001 | wc -l "

View number of HTTPS connections: 

    watch -n 5 " netstat -an | grep :443 | wc -l "

View SQL Queries running (on database server):

    sudo su
    watch -n 1 "mysql -e 'SHOW PROCESSLIST;'"
> This is helpful to see long running queries, and to identify any locks 

Logs: 
___

    /var/www/html/bodhi/storage/logs/echo.log - Laravel Echo STDOUT log 
    /var/www/html/bodhi/storage/logs/horizon.log - Artisan STDOUT log 
    /var/www/html/bodhi/storage/logs/echo.log - Laravel framework logging 
    /var/log/nginx/access.log - Nginx Access log 
    /var/log/nginx/error.log - Nginx service log

Troubleshooting: 
___

Nginx will not start: 
1. Run the command: 

        sudo service nginx status

2. Depending on the resulting error, you may need to view the Nginx Error Service log 

Supervisorctl shows that either echo or artisan will not start: 
1. View the log for either service 
2. Verify permissions on /var/www/html/bodhi directory 
3. Run the crontab artisan command interactively, verify that there is no exit code 1 closure 
4. Verify that processes are running:

        ps aux | grep artisan 
        ps aux | grep echo 

Laravel application is not writing error logs: 
1. Review the permissions in /var/www/html/bodhi/
2. Change the permissions to make sure that storage and bootstrap/cache are writable

        sudo chmod 777 -R /var/www/html/
        sudo chmod -R ug+rwx /var/www/html/bodhi/storage /var/www/html/bodhi/bootstrap/cache
