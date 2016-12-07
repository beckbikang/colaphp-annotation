简单的php框架

host 配置
127.0.0.1 cola2.other.program.php

网址配置
http://cola2.other.program.php/index/


nginx配置

        server {
        listen       80; 
        server_name  cola2.other.program.php;
        root /Users/kang/Documents/phpProject/otherproject/colaphp/app;
        index  index.html index.htm index.php;

        access_log  /Users/kang/Documents/var/log/access.log;


        #error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }   
            

        location ~ / { 
            if (!e $request_filename) {
                rewrite ^/(.*)$ /index.php/$1 break;
            }   

            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include        fastcgi_params;
            include         fastcgi.conf;
            fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
            fastcgi_param  PATH_INFO        $fastcgi_path_info;
        }   
        location ~ /\.ht {
            deny  all;
        }   
    } 
