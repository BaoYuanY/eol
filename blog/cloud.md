# Ubuntu 24.04 (Noble Numbat) 开发者服务器环境部署指南

本指南将指导您在全新的 Ubuntu 24.04 镜像上从零开始安装 Nginx、PHP 8.3、MySQL 8.0 和 Redis。

## 1. 基础系统更新

首先，更新系统软件包索引并升级已安装的包。

```bash
sudo apt update
sudo apt upgrade -y
```

## 2. 安装 Nginx

Ubuntu 24.04 默认仓库包含稳定的 Nginx 版本。

```bash
sudo apt install nginx -y

# 确认 Nginx 运行状态
sudo systemctl status nginx
```

## 3. 安装 MySQL

```bash
sudo apt install mysql-server -y

# 运行安全脚本（设置 root 密码、移除匿名用户等）
sudo mysql_secure_installation
```

## 4. 安装 PHP 8.3

Ubuntu 24.04 默认提供 PHP 8.3。对于 Laravel 项目，我们需要安装一些常用的扩展。

```bash
sudo apt install php-fpm php-mysql php-curl php-gd php-mbstring php-xml php-xmlrpc php-soap php-intl php-zip php-redis -y

# 确认 PHP 版本
php -v
```

## 5. 安装 Redis

```bash
sudo apt install redis-server -y

# 确认 Redis 运行状态
sudo systemctl status redis-server
```

## 6. 配置 Nginx 以运行 PHP

建议为您的项目创建一个新的 Nginx 配置文件。

```bash
# 创建配置文件（以 example.com 为例）
sudo vim /etc/nginx/sites-available/laravel

# 写入以下基础配置
server {
    listen 80;
    server_name your_domain_or_ip;
    root /var/www/your_project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# 启用配置并重启 Nginx
sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 7. 安装 Composer (可选，Laravel 必备)

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 8. 安装 Git 并配置 SSH Key (用于 GitHub)

### 8.1 安装 Git
```bash
sudo apt install git -y
```

### 8.2 生成 SSH Key
建议使用 Ed25519 算法，安全性更高：
```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
```
按三次回车键（使用默认文件路径且不设置密码）。

### 8.3 将 SSH Key 添加到 ssh-agent
```bash
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519
```

### 8.4 复制公钥并添加到 GitHub
查看并复制公钥内容：
```bash
cat ~/.ssh/id_ed25519.pub
```
复制输出的内容，前往 GitHub [SSH and GPG keys](https://github.com/settings/keys) 页面，点击 "New SSH key" 并粘贴。

### 8.5 测试连接
```bash
ssh -T git@github.com
```
看到 `Hi username! You've successfully authenticated...` 表示成功。

## 9. 防火墙配置 (UFW)

```bash
# 允许 SSH (非常重要，否则会断开连接)
sudo ufw allow OpenSSH

# 允许 HTTP 和 HTTPS
sudo ufw allow 'Nginx Full'

# 启用防火墙
sudo ufw enable
```

## 10. HTTPS 配置 (可选但推荐)

在生产环境中，建议为您的域名配置 SSL 证书。详细步骤请参考 [SSL 配置指南](ssl.md)。

## 11. 常用管理命令

- **Nginx**: `sudo systemctl restart nginx`
- **PHP-FPM**: `sudo systemctl restart php8.3-fpm`
- **MySQL**: `sudo systemctl restart mysql`
- **Redis**: `sudo systemctl restart redis-server`
