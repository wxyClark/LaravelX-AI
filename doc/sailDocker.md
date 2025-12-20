# Laravel Sail Docker 配置文档

## 概述

本项目基于 Laravel Sail 构建 Docker 环境，用于开发和部署 Laravel 应用程序。Sail 提供了一种简单的方式来启动包含所有必要服务的 Docker 环境。

## 配置过程

### 1. 安装 Laravel Sail

项目已通过 Composer 安装 Laravel Sail：

```bash
composer require laravel/sail --dev
```

### 2. 初始化 Sail 配置

运行以下命令初始化 Sail 配置：

```bash
php artisan sail:install
```

选择所需的服务（如 MySQL），Sail 会自动创建 `docker-compose.yml` 文件。

### 3. 环境变量配置

Sail 需要以下环境变量：

- `WWWUSER`: 运行容器的用户 ID
- `WWWGROUP`: 运行容器的组 ID
- `APP_PORT`: 应用程序端口（默认 80）

在 Windows PowerShell 中设置环境变量：

```powershell
$env:WWWUSER=1000
$env:WWWGROUP=1000
$env:APP_PORT=8080
```

### 4. 启动 Docker 容器

使用以下命令启动所有服务：

```bash
./vendor/bin/sail up -d
```

或者直接使用 Docker Compose：

```bash
docker-compose up -d
```

### 5. 数据库迁移

容器启动后，运行数据库迁移：

```bash
./vendor/bin/sail artisan migrate
```

## 服务架构

当前配置包含以下服务：

1. **laravel.test**: 主应用容器（基于 Ubuntu 24.04）
   - 映射端口: 80 → 80 (或指定的 APP_PORT)
   - 挂载卷: 项目目录挂载到 `/var/www/html`

2. **mysql**: MySQL 8.4 数据库服务
   - 映射端口: 3306 → 3306 (或指定的 FORWARD_DB_PORT)
   - 持久化存储: 使用 Docker 卷 `sail-mysql`

## 网络配置

所有服务都在 `sail` 网络中运行，可以相互访问。

## 常见问题及解决方案

### 1. 网络连接问题

**问题描述**: Docker 无法从远程仓库拉取基础镜像
**错误信息**: 
```
failed to do request: Head "https://registry-1.docker.io/v2/library/ubuntu/manifests/24.04": 
dialing registry-1.docker.io:443 container via direct connection because Docker Desktop has no HTTPS proxy
```

**解决方案**:
1. 检查网络连接和防火墙设置
2. 配置 Docker Desktop 代理设置（如果有公司代理）
3. 使用本地已有的镜像或从可信源拉取镜像

### 2. 环境变量未设置

**问题描述**: 启动容器时提示环境变量未设置
**解决方案**: 在启动容器前正确设置环境变量

### 3. 权限问题

**问题描述**: 容器无法访问挂载的文件
**解决方案**: 确保 `WWWUSER` 和 `WWWGROUP` 设置正确

## Telescope 验证

### 访问 Telescope 界面

启动容器后，可以通过以下 URL 访问 Telescope：

```
http://localhost:8080/telescope
```

### 验证步骤

1. 启动 Docker 容器
2. 确认应用正常运行
3. 访问 Telescope 界面
4. 查看是否有监控数据

## 停止和重启服务

### 停止服务

```bash
./vendor/bin/sail down
```

或

```bash
docker-compose down
```

### 重启服务

```bash
./vendor/bin/sail restart
```

或

```bash
docker-compose restart
```

## 故障排除

### 查看容器日志

```bash
./vendor/bin/sail logs
```

或查看特定服务日志：

```bash
./vendor/bin/sail logs laravel.test
```

### 进入容器

```bash
./vendor/bin/sail shell
```

## 性能优化建议

1. 使用 `.dockerignore` 文件排除不必要的文件
2. 合理配置容器资源限制
3. 定期清理无用的 Docker 镜像和容器

## 安全考虑

1. 生产环境应使用不同的配置
2. 数据库密码不应硬编码在配置文件中
3. 定期更新基础镜像和依赖包