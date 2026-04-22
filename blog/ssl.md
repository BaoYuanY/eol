# HTTPS 配置指南 (使用 Certbot)

本指南介绍如何在 Ubuntu 24.04 上使用 Let's Encrypt 提供的 Certbot 工具为 Nginx 配置 SSL 证书，实现 HTTP 到 HTTPS 的自动跳转。

## 1. 前提条件

- 您需要拥有一个已经解析到服务器公网 IP 的域名（例如 `example.com`）。
- Nginx 已按照 `cloud.md` 中的步骤安装并配置了基础的虚拟主机。

## 2. 安装 Certbot 和 Nginx 插件

在 Ubuntu 24.04 上，建议使用 `apt` 安装：

```bash
sudo apt update
sudo apt install certbot python3-certbot-nginx -y
```

## 3. 配置防火墙 (UFW)

确保防火墙允许 HTTPS (443) 流量。如果您已经按照 `cloud.md` 中的步骤操作，应该已经包含了 `Nginx Full`，这同时包含了 80 和 443 端口。

```bash
# 检查防火墙状态
sudo ufw status

# 如果没有允许 Nginx Full，请运行以下命令：
sudo ufw allow 'Nginx Full'
sudo ufw delete allow 'Nginx HTTP'
```

## 4. 获取并安装 SSL 证书

运行 Certbot 插件。它会自动检测 Nginx 配置文件中的 `server_name` 并引导您完成证书签发和自动配置过程。

```bash
sudo certbot --nginx -d your_domain.com -d www.your_domain.com
```

- **your_domain.com**: 替换为您的域名。
- **www.your_domain.com**: 替换为您的带 www 的子域名（可选）。

**提示：**
- 运行过程中，Certbot 会询问是否自动将 HTTP 流量重定向到 HTTPS，建议选择 **2 (Redirect)**。
- 您需要提供一个电子邮件地址以便接收证书过期提醒。

## 5. 验证自动续期

Let's Encrypt 的证书有效期为 90 天，Certbot 会通过定时任务（systemd timer）自动续期。您可以运行以下命令测试续期流程：

```bash
sudo certbot renew --dry-run
```
如果输出没有报错，则表示自动续期配置成功。

## 6. 更新 Laravel 配置

启用 HTTPS 后，请记得更新 Laravel 项目根目录下的 `.env` 文件：

```env
APP_URL=https://your_domain.com
```

---
*更多内容请参考 [cloud.md](cloud.md) 或 [mysql.md](mysql.md)*
