# MySQL 基础配置与远程访问

### 1. 创建远程用户

登录 MySQL：
```bash
sudo mysql
```

执行以下 SQL 命令（请替换 `your_username` 和 `your_password`）：
```sql
-- 创建允许所有 IP 登录的用户
CREATE USER 'your_username'@'%' IDENTIFIED BY 'your_password';

-- 创建数据库
CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 授予该用户对该数据库的所有权限
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'%';

-- 或者授予该用户所有数据库的所有权限
GRANT ALL PRIVILEGES ON *.* TO 'your_username'@'%' WITH GRANT OPTION;

-- 刷新权限
FLUSH PRIVILEGES;

-- 退出
EXIT;
```

### 2. 修改 MySQL 绑定地址以允许远程连接

默认情况下，MySQL 只监听本地回环地址 `127.0.0.1`。

```bash
# 编辑配置文件
sudo vim /etc/mysql/mysql.conf.d/mysqld.cnf
```

找到 `bind-address` 这一行，将其修改为 `0.0.0.0`：
```ini
bind-address            = 0.0.0.0
```

重启 MySQL 服务使配置生效：
```bash
sudo systemctl restart mysql
```

### 3. 防火墙配置 (UFW)

```bash
# 允许 MySQL 远程访问 (3306 端口)
sudo ufw allow 3306
```
