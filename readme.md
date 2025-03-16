# About Bodhi
Bodhi was developed by Codophile.


# Server Requirements
Several components of Bodhi rely on Redis/Horizon and/or the laravel-echo-server.  These services must be kept alive using a service like supervisor.  Note that for development on Homestead running on Windows, you may need to install npm and/or the laravel-echo-server from the Windows Subsystem for Linux rather than from within Homestead.  You need to run laravel-echo-server init from the root of the project to create the laravel-echo-server.json configuration file.
You will also need to do an npm install to get some other depedencies (e.g. noty).
To use scheduled actions, you need to add one line to your crontab "crontab -e": * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

## PHP Configuration
If large files are going to be uploaded (e.g. for the peer review system), you may need to update some of the directives in the php.ini file to allow larger file sizes for uploads.
Note that there are likely multiple php.ini files (one for CLI and another for the web server) and you need to update the one for the web server.  To find the web server location, create a page that displays phpinfo().
Update the directives for upload_max_filesize and post_max_size to the appropriate values for the desired file sizes.

## Supervisor Configuration Files
The following configuration files are examples that can be placed in /etc/supervisor/conf.d

bodhi-echo.conf
```
[program:bodhi-echo]
directory=/home/vagrant/code/bodhi
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/laravel-echo-server start
autostart=true
autorestart=true
user=vagrant
numprocs=1
redirect_stderr=true
stdout_logfile=/home/vagrant/code/bodhi/storage/logs/echo.log
```
bodhi-horizon.conf
```
[program:bodhi-horizon]
process_name=%(program_name)s
command=php /home/vagrant/code/bodhi/artisan horizon
autostart=true
autorestart=true
user=vagrant
redirect_stderr=true
stdout_logfile=/home/vagrant/code/bodhi/storage/logs/horizon.log
```

## Socket Server Configuration
laravel-echo-server must be configured correctly for the socket traffic to work.

Note that the following nginx or Apache configruation is only required if you're planning to proxy socket traffic through http rather than have its own port.

### Nginx
Proxying this way can cause problems with access to http requests if the server gets busy, so it is recommended not to use this approach.

nginx configuration file (in sites-available)
```
#the following would go within the server{} block of your web server config
location /socket.io {
	    proxy_pass http://127.0.0.1:6001; #this assumes Echo and NginX are on the same box
	    proxy_http_version 1.1;
	    proxy_set_header Upgrade $http_upgrade;
	    proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
	}
```

### Apache
Apache configuration file
```
#this goes inside the <VirtualHost> block
<location /socket>
        RewriteEngine On
        RewriteCond %{QUERY_STRING} transport=websocket    [NC]
        RewriteRule /(.*)           ws://localhost:6001/socket.io/%{QUERY_STRING} [P,L]
        ProxyPass http://localhost:6001
        ProxyPassReverse http://localhost:6001
</location>
```

The corresponding Echo declaration in bootstrap.js (which gets added to app.js using npm run) is:
```
window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: { path: '/socket/socket.io' },
});
```

### Echo Server Configuration
Example of laravel-echo-server.json configuration file.  Note that the current configuration is set up for an application that is in the bodhi subfolder rather than the domain root (i.e., it's at https://host/bodhi/ rather than at https://host/).  The only difference is the authEndpoint parameter, which is normally "/broadcasting/auth"
```
{
        "authHost": "https://atom.calpoly.edu",
        "authEndpoint": "/bodhi/broadcasting/auth",
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
        "protocol": "http",
        "socketio": {},
        "sslCertPath": "",
        "sslKeyPath": "",
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
```

## Configuration
