# Xdebug 安装与配置指南

本文档详细记录了在 Windows 环境下为 PHP 8.5 安装和配置 Xdebug 扩展的完整过程。

## 环境信息

- **操作系统**: Windows 11
- **PHP 版本**: 8.5.0 (线程安全版本)
- **架构**: x64
- **编译器**: Visual C++ 2022

## 安装步骤

### 1. 确定 PHP 版本信息

首先，我们需要获取 PHP 的详细版本信息，以确保下载正确的 Xdebug 扩展版本：

```bash
php -i | findstr "PHP Version"
php -i | findstr "Thread Safety"
php -i | findstr "Architecture"
php -i | findstr "Compiler"
```

输出结果示例：
```
PHP Version => 8.5.0
Thread Safety => enabled
Architecture => x64
Compiler => Visual C++ 2022
```

### 2. 下载 Xdebug 扩展

根据 PHP 版本信息，我们从 Xdebug 官网下载兼容的扩展文件。由于 Xdebug 3.4.5 是为 PHP 8.4 编译的，这是最接近 PHP 8.5 的可用版本。

```bash
# 下载适用于 PHP 8.4 TS VS17 (64 bit) 的 Xdebug 扩展
Invoke-WebRequest -Uri "https://xdebug.org/files/php_xdebug-3.4.5-8.4-ts-vs17-x86_64.dll" -OutFile "php_xdebug.dll"
```

### 3. 安装 Xdebug 扩展

将下载的扩展文件移动到 PHP 的扩展目录：

```bash
# 将扩展文件移动到 ext 目录
mv php_xdebug.dll ext\
```

验证文件是否已正确放置：

```bash
ls ext\php_xdebug.dll
```

### 4. 配置 php.ini

在 `php.ini` 文件中添加 Xdebug 配置：

```ini
extension=php_xdebug.dll
xdebug.mode=debug
xdebug.start_with_request=yes
```

可以通过以下命令添加配置：

```bash
echo extension=php_xdebug.dll >> php.ini
echo xdebug.mode=debug >> php.ini
echo xdebug.start_with_request=yes >> php.ini
```

### 5. 验证安装

重启 Web 服务器后，验证 Xdebug 是否已正确加载：

```bash
php -v
php -m | findstr xdebug
```

预期输出应包含 Xdebug 信息：

```
PHP 8.5.0 (cli) (built: Nov 18 2025 08:17:59) (ZTS Visual C++ 2022 x64)
Copyright (c) The PHP Group
Zend Engine v4.5.0, Copyright (c) Zend Technologies
    with Xdebug v3.4.5, Copyright (c) 2002-2025, by Derick Rethans
    with Zend OPcache v8.5.0, Copyright (c), by Zend Technologies
```

## 故障排除

如果 Xdebug 没有正确加载，请检查以下几点：

1. **版本兼容性**：确认下载的 Xdebug 版本与 PHP 版本兼容
2. **文件完整性**：确保扩展文件未损坏
3. **路径配置**：确认 `php.ini` 中的扩展路径正确
4. **权限问题**：确保 PHP 有权限读取扩展文件

## 注意事项

- 当前 Xdebug 3.4.5 是为 PHP 8.4 编译的，可能与 PHP 8.5 存在兼容性问题
- 建议关注 Xdebug 官方网站，等待正式支持 PHP 8.5 的版本发布
- 在生产环境中使用 Xdebug 可能会影响性能，请谨慎配置

## 相关链接

- [Xdebug 官网](https://xdebug.org/)
- [Xdebug 下载页面](https://xdebug.org/download)
- [Xdebug 安装文档](https://xdebug.org/docs/install)