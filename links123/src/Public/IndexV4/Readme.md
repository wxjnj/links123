#Index V4 
====

## 目录结构
	
	base: links123/src/Public/IndexV4/
	
		dest/			=> Grunt构建
		src/			=> 开发目录
		node_modules/	=> Grunt插件 不放入git
		
## LESS逻辑

1. style.less为基本样式入口文件（包括宽窄屏处理 & 不包括主题处理）
2. style.less里声明的变量为全局变量。
3. 基本样式的其他less文件都使用import导入style.less，简化html中引用
4. theme-{name}.less为主题样式文件

## HTML引用静态资源（开发 & 构建状态）
静态文件的引用位置：

	base: links123/src/App/Tpl/Home/Contorls/
		statics_v4_dev.html		=>	开发文件引用
		statics_v4_build.html	=>	构建文件引用

修改HTML模板，引用不同状态的静态资源：
	
	<include file="Contorls:statics_v4_dev" />
	or
	<include file="Contorls:statics_v4_build" />

**增删静态文件，注意修改Grunt配置和HTML里的引用**


====

##grunt构建说明


### 安装Node.js

- [Node.js](http://nodejs.org/)
- 最新版本的nodejs自带NPM(包管理工具)

### 前提条件

- 确保本地已经获取到最新的`Gruntfile.js`和`package.json`

### 构建准备

- 打开`Node.js command prompt`

- cd 到`links123/src/Public/IndexV4`目录

- **首次使用**需要通过NPM安装grunt和grunt的插件

```
~/links123/src/Public/IndexV4> npm install -g grunt-cli
~/links123/src/Public/IndexV4> npm install
```
- 安装完成后`linksFrontEnd`目录会多出`node_modules`文件夹，该文件夹无需提交到git

### 开始构建

- 输入grunt会自动构建，但为了防止错误操作，默认键入`grunt`不做任何操作，请输入构建目标，当前主站前端项目的构建目标为`build`

```
~/links123/src/Public/IndexV4> grunt build
```

- 出现`Done，without errors`表示构建成功


