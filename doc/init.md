# Laravel 项目初始化脚本

本文档记录了在 `init` 分支上执行的 Laravel 项目初始化过程，包括依赖安装、环境配置和 Docker 配置等步骤。

## 1. 创建并切换到 init 分支

```shell
git checkout -b init
```

## 2. 配置环境文件

```shell
cp .env.example .env
```

## 3. 安装 PHP 扩展

检查并安装必要的 PHP 扩展以支持 Laravel 框架的正常运行：

```shell
# 检查当前已安装的扩展
php -m

# 在 php.ini 中启用以下扩展
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=soap
extension=zip
```

## 4. 安装 Composer 依赖包

```shell
composer install
```

## 5. 安装 npm 依赖包

```shell
npm install
```

## 6. 生成应用密钥

```shell
php artisan key:generate
```

## 7. 配置 Docker 和 Laravel Sail

```shell
# 安装 Laravel Sail 并选择 MySQL 作为数据库服务
php artisan sail:install
# 选择选项 1 (mysql)
```

## 8. 提交更改到 Git

```shell
git add .
git commit -m "Initialize Laravel project with dependencies and Docker configuration"
```

## 9. 启动 Docker 容器

完成上述步骤后，您可以使用以下命令启动 Docker 容器：

```shell
./vendor/bin/sail up
```

## 10. 数据库迁移

首次启动容器后，运行数据库迁移命令：

```shell
./vendor/bin/sail artisan migrate
```

以上步骤完成了 Laravel 项目的完整初始化过程，包括前后端依赖安装、环境配置以及 Docker 化部署配置。