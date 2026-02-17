# 影视 PHP CMS

一个开箱即用的轻量级影视内容管理系统（PHP + SQLite），支持：

- 前台影片展示、筛选、搜索
- 影片详情页
- 后台登录
- 分类管理
- 影片新增、编辑、删除

## 运行要求

- PHP 8.0+
- 开启 PDO SQLite 扩展

## 快速启动

```bash
php -S 0.0.0.0:8000
```

浏览器访问：

- 前台首页：`http://localhost:8000`
- 后台入口：`http://localhost:8000/admin/login.php`

默认后台账号：

- 用户名：`admin`
- 密码：`admin123`

## 目录结构

- `index.php`：前台首页
- `movie.php`：影片详情
- `admin/`：后台页面
- `lib/`：数据库与认证逻辑
- `data/`：SQLite 数据文件（首次运行自动生成）
